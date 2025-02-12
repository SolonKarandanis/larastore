<?php

namespace App\Gateway;

use App\Models\Order;
use Illuminate\Http\Request;

interface GatewayInterface
{
    public function purchase(Order $order,Request $request);
}
