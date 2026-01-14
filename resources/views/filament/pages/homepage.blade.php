<x-filament::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}

        <div class="button-wrapper">
            <x-filament::button type="submit">
                Guardar
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
