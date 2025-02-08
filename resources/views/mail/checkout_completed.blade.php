<x-mail::message>
    <h1 style="text-align: center; font-size: 24px">
        Payment was Completed Successfully.
    </h1>
    @foreach($orders as $order)
        <x-mail::table>
            <table>
                <tbody>
                    <tr>
                        <td>Seller</td>
                        <td>
                            <a href="{{url('/')}}">
                                {{$order->vendorUser->vendor->store_name}}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>Order #</td>
                        <td>{{$order->id}}</td>
                    </tr>
                    <tr>
                        <td>Items/td>
                        <td>{{$order->orderItems}}</td>
                    </tr>
                    <tr>
                        <td>Order Total</td>
                        <td>{{\Illuminate\Support\Number::currency($order->total_price)}}</td>
                    </tr>
                </tbody>
            </table>
        </x-mail::table>
        <x-mail::table>

        </x-mail::table>
    @endforeach
</x-mail::message>

