<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

class CheckoutStatus extends Component
{
    public $sessionId;

    public function mount()
    {
        $this->sessionId = request()->get('session_id');
    }

    #[Computed]
    public function order()
    {
        if (! $this->sessionId) return null;
        
        $order = Order::where('stripe_checkout_session_id', $this->sessionId)->first();
        if (! $order) return null;

        // si el usuario esta logueado, la orden debe pertenecerle
        if (Auth::check()) {
            return $order->user_id === Auth::id() ? $order : null;
        }

        // si es invitado, invalida contra la sesion local
        $sid = session()->getId();
        return $order->guest_session_id && $order->guest_session_id === $sid ? $order : null;
    }

    public function render()
    {
        return view('livewire.checkout-status');
    }
}
