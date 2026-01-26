<div class="container">
    @if ($this->order)
        <h2>Gracias por tu compra (#{{ $this->order->id }})</h2>
        
        <p>Recibirás un correo de confirmación. Si no llega, puedes guardar este ticket como comprobante de compra.</p>
        
        <div class="status-shell">
            <button type="button" class="button no-print" onclick="window.print()">
                Imprimir / Guardar PDF
            </button>

            <h3>Resumen de tu compra:</h3>
            @foreach ($this->order->items as $item)
                <p> {{ $item->name }} </p>
                <p> {{ $item->quantity }} </p>
                <p> {{ $item->amount_subtotal }} </p>
                <p> {{ $item->amount_tax }} </p>
                <p> {{ $item->amount_total }} </p>
            @endforeach
        </div>

        @auth
            <p>
                <a href="{{ route('my-orders') }}" class="underline">Tu recibo de compra</a>
            </p>
        @else
            <p>¿Quieres ver tu historial? Crea una cuenta</p>
            <a class="button" data-type="cart" href="{{ route('register') }}">Crear cuenta</a>
        @endauth
    @else
        <p wire:poll>
            Esperando la confirmación del pago. Por favor, espera...
        </p>
    @endif
</div>
