<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\VariationType;
use App\Models\VariationTypeOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CartService
{
    private ?array $cachedCartItems = null;
    protected const COOKIE_NAME = 'cartItems';
    protected const COOKIE_LIFETIME = 60*24*365; // 1 year
    public function addItemToCart(Product $product, int $quantity = 1, $optionsIds = null){
        if($optionsIds !== null){
            $optionIds = $product->variationTypes
                ->mapWithKeys(fn(VariationType $type)=>[$type->id => $type->options[0]?->id])
                ->toArray();
        }
        $price = $product->getPriceForOptions($optionsIds);
        if(Auth::check()){
            $this->saveItemToDatabase($product->id, $quantity, $price,$optionsIds);
        }
        else{
            $this->saveItemToCookies($product->id, $quantity, $price,$optionsIds);
        }
    }

    public function updateItemQuantity(int $productId,int $quantity,$optionsIds = null){
        if(Auth::check()){
            $this->updateItemQuantityinDatabase($productId, $quantity, $optionsIds);
        }
        else{
            $this->updateItemQuantityInCookies($productId, $quantity, $optionsIds);
        }
    }

    public function removeItemFromCart(int $productId, $optionsIds = null){
        if(Auth::check()){
            $this->deleteItemFromDatabase($productId, $optionsIds);
        }
        else{
            $this->deleteItemFromCookies($productId, $optionsIds);
        }
    }

    public function getCartItems():array{
        try{
            if($this->cachedCartItems === null){
                if(Auth::check()){
                    $cartItems = $this->getCartItemsFromDatabase();
                }
                else{
                    $cartItems = $this->getCartItemsFromCookies();
                }
                $productIds = collect($cartItems)->map(fn($item) => $item['product_id']);
                $products = Product::whereIn('id', $productIds)
                    ->with('user.vendor')
                    ->forWebsite()
                    ->get()
                    ->keyBy('id');
                $cartItemData=[];
                foreach($cartItems as $key=>$cartItem){
                    $product = data_get($products, $cartItem['product_id']);
                    if(!$product) continue;
                    $optionInfo =[];
                    $options = VariationTypeOption::with('variationType')
                        ->whereIn('id', $cartItem['option_ids'])
                        ->get()
                        ->keyBy('id');

                    $imageUrl = null;
                    foreach($cartItem['option_ids'] as $option_id){
                        $option = data_get($options, $option_id);
                        if(!$imageUrl){
                            $imageUrl = $option->getFirstMediaUrl('images','small');
                        }
                        $optionInfo[] = [
                            'id' => $option_id,
                            'name' => $option->name,
                            'type' => [
                                'id' => $option->variationType->id,
                                'name' => $option->variationType->name,
                            ],
                        ];
                    }

                    $cartItemData[] = [
                        'id' => $cartItem['id'],
                        'product_id' => $product->id,
                        'title' => $product->title,
                        'slug' => $product->slug,
                        'price' => $cartItem['price'],
                        'quantity' => $cartItem['quantity'],
                        'option_ids'=> $cartItem['option_ids'],
                        'options' => $optionInfo,
                        'image' => $imageUrl ? $imageUrl : $product->getFirstMediaUrl('images','small'),
                        'user' =>[
                            'id'=> $product->created_by,
                            'name' => $product->user->vendor->store_name
                        ]
                    ];
                }
                $this->cachedCartItems = $cartItemData;
            }
            return $this->cachedCartItems;
        }catch (\Exception $exception){
            Log::error($exception->getMessage().PHP_EOL.$exception->getTraceAsString());
        }
        return [];
    }

    public function getTotalQuantity():int{
        $totalQuantity = 0;
        foreach($this->getCartItems() as $cartItem){
            $totalQuantity += $cartItem['quantity'];
        }
        return $totalQuantity;
    }

    public function getTotalPrice():float{
        $totalPrice = 0;
        foreach($this->getCartItems() as $cartItem){
            $totalPrice += $cartItem['quantity']*$cartItem['price'];
        }
        return $totalPrice;
    }

    protected function updateItemQuantityinDatabase(int $productId,int $quantity,array $optionsIds):void{
        $userId=Auth::id();
        $cartItem = CartItem::where('user_id',$userId)
            ->where('product_id',$productId)
            ->where('variation_type_option_ids',json_encode($optionsIds))
            ->first();
        if($cartItem){
            $cartItem->update([
                'quantity' => $quantity,
            ]);
        }
    }

    protected function updateItemQuantityInCookies(int $productId,int $quantity,array $optionsIds):void{
        $cartItems = $this->getCartItemsFromCookies();
        ksort($optionsIds);
        //Use a unique key based on product ID and option IDs
        $itemKey = $productId.'_'.json_encode($optionsIds);

        if(isset($cartItems[$itemKey])){
            $cartItems[$itemKey]['quantity'] = $quantity;
        }
        //Save updated cart items to the cookie
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    protected function saveItemToDatabase(int $productId,int $quantity,int $price,array $optionsIds):void{
        $userId=Auth::id();
        ksort($optionsIds);
        $cartItem = CartItem::where('user_id',$userId)
            ->where('product_id',$productId)
            ->where('variation_type_option_ids',json_encode($optionsIds))
            ->first();
        if($cartItem){
            //add the quantity to the existing one in DB
            $cartItem->update([
                'quantity' => DB::raw('quantity+'.$quantity),
            ]);
        }
        else{
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'variation_type_option_ids' => json_encode($optionsIds),
                'quantity' => $quantity,
                'price' => $price
            ]);
        }
    }

    protected function saveItemToCookies(int $productId,int $quantity,int $price,array $optionsIds):void{
        $cartItems = $this->getCartItemsFromCookies();
        ksort($optionsIds);
        $itemKey = $productId.'_'.json_encode($optionsIds);
        if(isset($cartItems[$itemKey])){
            $cartItems[$itemKey]['quantity'] += $quantity;
            $cartItems[$itemKey]['price'] = $price;
        }
        else{
            $cartItems[$itemKey] = [
                'id'=> Str::uuid(),
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'option_ids' => $optionsIds,
            ];

            Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
        }
    }

    protected function deleteItemFromDatabase(int $productId,array $optionsIds):void{
        $userId=Auth::id();
        ksort($optionsIds);
        CartItem::where('user_id',$userId)
            ->where('product_id',$productId)
            ->where('variation_type_option_ids',json_encode($optionsIds))
            ->delete();
    }

    protected function deleteItemFromCookies(int $productId,array $optionsIds):void{
        $cartItems= $this->getCartItemsFromCookies();
        ksort($optionsIds);
        $cartKey = $productId.'_'.json_encode($optionsIds);

        //Remove item from cart
        unset($cartItems[$cartKey]);
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    protected function getCartItemsFromDatabase():array{
        $userId=Auth::id();
        $cartItems = CartItem::where('user_id',$userId)
            ->get()
            ->map(function($cartItem){
                return [
                    'id'=> $cartItem->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'option_ids' => json_decode($cartItem->variation_type_option_ids,true),
                ];
            })
            ->toArray();
//        dd($cartItems);
        return $cartItems;
    }

    protected function getCartItemsFromCookies():array{
        $cartItems= json_decode(Cookie::get(self::COOKIE_NAME,'[]'),true);
        return $cartItems;
    }

    public function getCartItemsGrouped():array{
        $cartItems = $this->getCartItems();
        return collect($cartItems)
            ->groupBy(fn($item)=>$item['user']['id'])
            ->map(fn($items, $userId)=>[
                'user'=>$items->first()['user'],
                'items'=>$items->toArray(),
                'totalQuantity'=>$items->sum('quantity'),
                'totalPrice'=>$items->sum(fn($item)=>$item['price'] * $item['quantity']),
            ])
            ->toArray();
    }

    public function moveCartItemsToDatabase(int $userId):void{
        $cartItems = $this->getCartItemsFromCookies();

        // Loop through the cart items and insert them into the database
        foreach ($cartItems as $itemKey => $cartItem){
            // Check if the cart item already exists for the user
            $existingItem = CartItem::where('user_id',$userId)
                ->where('product_id',$cartItem['product_id'])
                ->where('variation_type_option_ids',json_encode($cartItem['option_ids']))
                ->first();
            if($existingItem){
                $existingItem->update([
                    'quantity'=> $existingItem->quantity + $cartItem['quantity'],
                    'price'=> $cartItem['price'],
                ]);
            }
            else{
                CartItem::create([
                    'user_id' => $userId,
                    'product_id' => $cartItem['product_id'],
                    'variation_type_option_ids' => $cartItem['option_ids'],
                    'quantity' => $cartItem['quantity'],
                    'price' => $cartItem['price']
                ]);
            }
        }

        //After transferring the items, delete the cart from the cookies
        Cookie::queue(self::COOKIE_NAME,'',-1);
    }
}
