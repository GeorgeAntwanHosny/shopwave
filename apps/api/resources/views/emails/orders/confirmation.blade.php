@component('mail::message')
# Order Confirmed

Thanks for your order from **{{ $order->vendor->shop_name }}**.

@component('mail::table')
| Item | Qty | Price |
| :--- | :-: | ----: |
@foreach ($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | ${{ $item->price }} |
@endforeach
@endcomponent

**Total: ${{ $order->total }}**

@component('mail::button', ['url' => config('app.frontend_url') . '/orders/' . $order->id])
View Order
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
