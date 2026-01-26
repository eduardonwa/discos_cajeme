@component('mail::message')
Hey {{ $order->user?->name ?? ($order->billing_address['name'] ?? 'Invitado') }}

Gracias por tu orden. Puedes consultar los detalles a continuación.

@component('mail::table')
    | Item                                  | Price     | Quantity     | Tax    | Total     |
    | :------------------------------------ |----------:|-------------:|-------:|----------:|
    @foreach($order->items as $item)
        | **{{ $item->name }}** <br> @if($item->variant) @foreach ($item->variant->attributes as $attributeVariant) {{ $attributeVariant->attribute->key }}: {{ $attributeVariant->value }} @endforeach @else Producto unitario @endif | {{ $item->price }} | {{ $item->quantity }} | {{ $item->amount_tax }} | {{ $item->amount_total }} |
    @endforeach
    @if ($order->amount_shipping->isPositive())
        | | | | **Shipping** | {{ $order->amount_shipping }} |
    @endif
    @if ($order->amount_discount->isPositive())
        | | | | **Discount** | @if ($order->coupon_code) ({{ $order->coupon_code }}) @endif | {{ $order->amount_discount }} |
    @endif
    @if ($order->amount_tax->isPositive())
        | | | | **Tax** | {{ $order->amount_tax }} |
    @endif
    | | | | **Subtotal** | {{ $order->amount_subtotal }} |
    | | | | **Total** | {{ $order->amount_total }} |
@endcomponent
@component('mail::button', ['url' => route('view-order', $order->id), 'color' => 'success'])
    View Order
@endcomponent
@endcomponent