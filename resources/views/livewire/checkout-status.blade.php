<div class="checkout-status | container">
    @if ($this->order)
        <header>
            <h2 class="heading-3">¡Gracias por tu compra! (#{{ $this->order->id }})</h2>
            <p class="fs-500">
                Recibirás un correo de confirmación en <span class="ff-bold">{{ $this->order->customer_email }}</span>.
                Si no tienes cuenta, guarda este ticket como comprobante.
            </p>
            <button type="button" data-type="ghost" class="button no-print" onclick="window.print()">
                Imprimir / Guardar PDF
            </button>
        </header>
        
        <main class="checkout-status__ticket">
            <h3 class="fs-600">Resumen</h3>

            <div class="items-wrapper">
                <dl class="items">
                    @foreach ($this->order->items as $item)
                        <h2 class="heading-3">{{ $item->name }}</h2>

                        <div class="item">
                            <dt>Cantidad</dt>
                            <dd>{{ $item->quantity }}</dd>

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

            <dl class="totals">
                <div class="row">
                    <dt>Subtotal</dt>
                    <dd>{{ $this->order->amount_subtotal }}</dd>
                </div>
                <div class="row">
                    <dt>Impuestos</dt>
                    <dd>{{ $this->order->amount_tax }} (16%)</dd>
                </div>
                <div class="row">
                    <dt>Total</dt>
                    <dd>{{ $this->order->amount_total }}</dd>
                </div>
            </dl>
        </main>

        <section class="checkout-status__order | no-print">
            @auth
                <div class="auth">
                    <a href="{{ route('my-orders') }}" class="button" data-type="primary">Ver órdenes</a>
                </div>
            @else
                <div class="guest | no-print">
                    <p class="fs-500">¿Quieres ver tu historial? Crea una cuenta</p>
                    <a class="button" data-type="primary" href="{{ route('register') }}">Crear cuenta</a>
                </div>
            @endauth
    @else
            <p class="text-center fs-500" wire:poll>
                Esperando la confirmación del pago. Por favor, espera...
            </p>
        </section>
    @endif
</div>
