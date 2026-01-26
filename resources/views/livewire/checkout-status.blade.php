<div class="checkout-status | container">
    @if ($this->order)
        <header>
            <h2 class="heading-3">¡Gracias por tu compra! (#{{ $this->order->id }})</h2>
            <p class="fs-500">Recibirás un correo de confirmación. Si no tienes cuenta, guarda este ticket como comprobante.</p>
            <button type="button" data-type="ghost" class="button no-print" onclick="window.print()">
                Imprimir / Guardar PDF
            </button>
        </header>
        
        <main class="checkout-status__ticket">
            <h3 class="fs-600">Resumen</h3>

            <div class="items-wrapper">
                <dl class="items">
                    @foreach ($this->order->items as $item)                       
                        <div class="item">
                            <dt>Artículo</dt>
                            <dd>{{ $item->name }}</dd>

                            <dt>Cantidad</dt>
                            <dd>{{ $item->amount_subtotal }}</dd>

                            <dt>Subtotal</dt>
                            <dd>{{ $item->amount_subtotal }}</dd>

                            <dt>Impuestos</dt>
                            <dd>{{ $item->amount_tax }}</dd>
                            
                            <dt>Total</dt>
                            <dd>{{ $item->amount_total }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </main>

        <section class="checkout-status__order">
            @auth
                <div class="auth">
                    <a href="{{ route('my-orders') }}" class="underline">Tu recibo de compra</a>
                </div>
            @else
                <div class="guest | no-print">
                    <p class="fs-500">¿Quieres ver tu historial? Crea una cuenta</p>
                    <a class="button" data-type="cart" href="{{ route('register') }}">Crear cuenta</a>
                </div>
            @endauth
    @else
            <p class="text-center fs-500" wire:poll>
                Esperando la confirmación del pago. Por favor, espera...
            </p>
        </section>
    @endif
</div>
