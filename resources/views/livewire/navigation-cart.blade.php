<div>
    <x-nav-link wire:navigate href="{{ route('cart') }}" :active="request()->routeIs('cart')">
        <x-icon data-type="cart" :size="24" decorative fill="#344D55">
            <x-ui.icons.cart />
            <span>{{ $this->count }}</span>
        </x-icon> 
    </x-nav-link>
</div>