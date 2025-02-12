<?php

namespace App\Gateway;

use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class StripeGateway implements GatewayInterface
{

    public function purchase(Order $order, Request $request)
    {
        $stripe_client = new StripeClient(config('app.stripe_secret_key'));
        $endpoint_secret= config('app.stripe_webhook_secret_key');
    }
}
