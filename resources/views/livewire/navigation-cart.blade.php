<div>
    <x-nav-link wire:navigate href="{{ route('cart') }}" :active="request()->routeIs('cart')">
        <x-icon data-type="cart" :size="24" decorative fill="#344D55">
            <x-ui.icons.cart />
            @if ($this->count > 0)
                <span wire:key="cart-badge-{{ $bump }}">{{ $this->count }}</span>
            @endif
        </x-icon>
    </x-nav-link>
</div>
