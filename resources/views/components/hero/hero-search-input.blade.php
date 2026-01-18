<div class="{{ $class ?? 'hero-input-search' }}">
    <x-input wire:model.live.debounce="searchQuery" type="text" placeholder="Escribe algo"/>
    <div wire:loading.delay.shorter wire:target="searchQuery">Buscando...</div>
</div>