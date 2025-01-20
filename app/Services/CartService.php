<?php

namespace App\Services;

use App\Models\Product;

class CartService
{
    private ?array $cachedCartItems = null;
    protected const COOKIE_NAME = 'cartItems';
    protected const COOKIE_LIFETIME = 60*24*365; // 1 year
    public function addItemToCart(Product $product, int $quantity = 1, $optionsIds = null){

    }

    public function updateItemQuantity(int $productId,int $quantity,$optionsIds = null){

    }

    public function removeItemFromCart(int $productId, $optionsIds = null){

    }

    public function getCartItems():array{
        try{

        }catch (\Exception $exception){

        }
    }

    public function getTotalQuantity():int{

    }

    public function getTotalPrice():float{

    }

    protected function updateItemQuantityinDatabase(int $productId,int $quantity,array $optionsIds):void{

    }

    protected function updateItemQuantityInCookies(int $productId,int $quantity,array $optionsIds):void{

    }

    protected function saveItemToDatabase(int $productId,int $quantity,array $optionsIds):void{

    }

    protected function saveItemToCookies(int $productId,int $quantity,array $optionsIds):void{

    }

    protected function deleteItemFromDatabase(int $productId,int $optionsIds):void{

    }

    protected function deleteItemFromCookies(int $productId,int $optionsIds):void{

    }

    protected function getCartItemsFromDatabase():array{

    }

    protected function getCartItemsFromCookies():array{

    }
}
