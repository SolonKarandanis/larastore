<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Http\Resources\OrderViewResource;
use App\Mail\CheckoutCompleted;
use App\Mail\NewOrderMail;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\PaymentSuccessful;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function success(Request $request){
        $user= auth()->user();
        $session_id=$request->get('session_id');
        $orders = Order::where('stripe_session_id',$session_id)->get();

        if($orders->isEmpty()){
            abort(404);
        }

        foreach ($orders as $order){
            if($order->user_id !== $user->id){
                abort(403);
            }
        }

        return Inertia::render('Stripe/Success',[
            'orders'=>OrderViewResource::collection($orders)->collection->toArray(),
        ]);
    }

    public function failure(){

    }

    public function webhook(Request $request){
        $stripe_client = new StripeClient(config('app.stripe_secret_key'));
        $endpoint_secret= config('app.stripe_webhook_secret_key');
        $payload= $request->getContent();
        $signature_header = request()->header('Stripe-Signature');
        $event = null;

        try{
           $event= Webhook::constructEvent($payload, $signature_header, $endpoint_secret);

        }
        catch(\UnexpectedValueException $e){
            Log::error($e);
            //invalid payload
            return response('Invalid Payload', 400);
        }
        catch(SignatureVerificationException $e){
            Log::error($e);
            return response('Invalid Payload', 400);
        }

        //Handle the event
        switch ($event->type) {
            case 'charge.updated':
                $charge = $event->data->object;
                $transactionId=$charge['balance_transaction'];
                $paymentIntent=$charge['payment_intent'];
                $balanceTransaction=$stripe_client->balanceTransactions->retrieve($transactionId);

                $orders = Order::where('payment_intent',$paymentIntent)
                    ->get();
                $totalAmount = $balanceTransaction['amount'];
                $stripeFee =0;
                foreach ($balanceTransaction['fee_details'] as $fee_detail){
                    if($fee_detail['type']=='stripe_fee'){
                        $stripeFee=$fee_detail['amount'];
                    }
                }
                $platformFeePercentage=config('app.platform_fee_percentage');

                foreach ($orders as $order){
                    $vendorShare = $order->total_price / $totalAmount;

                    $order->online_payment_commission = $vendorShare * $stripeFee;
                    $order->website_commission = ($order->total_price - $order->online_payment_commission)/100 * $platformFeePercentage;
                    $order->vendor_subtotal=$order->total_price - $order->online_payment_commission-$order->website_commission;
                    $order->save();

                    $users_to_be_notified=[];
                    $vendor = $order->vendor;
                    $users_to_be_notified[]=$vendor;
                    $users_to_be_notified[]=$order->user;
                    Notification::send($users_to_be_notified, new PaymentSuccessful($order));
                    Mail::to($vendor)->send(new NewOrderMail($order));
                }
                $buyer = $orders[0]->user;
                Mail::to($buyer)->send(new CheckoutCompleted($orders));

            case 'checkout.session.completed':
                $session = $event->data->object;
                $pi=$session['payment_intent'];

                $orders = Order::query()
                    ->with(['orderItems'])
                    ->where(['stripe_session_id'=>$session['id']])
                    ->get();

                $productToBeDeletedFromCart=[];

                foreach ($orders as $order){
                    $order->payment_intent=$pi;
                    $order->status=OrderStatusEnum::Paid;
                    $order->save();

                    $productToBeDeletedFromCart=[
                        ...$productToBeDeletedFromCart,
                        ...$order->orderItems->map(fn($item)=> $item->product_id)->toArray()
                    ];

                    //Reduce product quantity
                    foreach ($order->orderItems as $orderItem){
                        /** @var OrderItem $orderItem */
                        $options = $orderItem->variation_type_option_ids;
                        $product = $orderItem->product;
                        if($options){
                            sort($options);
                            $variation = $product->variations()
                                ->where('variation_type_option_ids',$options)
                                ->first();
                            if($variation && $variation->quantity != null){
                                $variation->quantity -= $orderItem->quantity;
                                $variation->save();
                            }
                        }
                        else if($product->quantity != null){
                            $product->quantity -=$orderItem->quantity;
                            $product->save();
                        }
                    }

                    CartItem::query()
                        ->where('user_id',$order->user_id)
                        ->whereIn('product_id',$productToBeDeletedFromCart)
                        ->where('saved_for_later',false)
                        ->delete();
                }
            default :
                echo 'Received unknown event type '. $event->type;
        }
        return response('', 200);
    }

    public function connect()
    {
        if(!auth()->user()->getStripeAccountId()){
            auth()->user()->createStripeAccount(['type'=>'standard']);
        }

        if(!auth()->user()->isStripeAccountActive()){
            return redirect(auth()->user()->getStripeAccountLink());
        }

        return back()->with('success','Your stripe account is already connected.');
    }
}
