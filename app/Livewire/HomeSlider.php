<?php

namespace App\Livewire;

use Livewire\Component;

class HomeSlider extends Component
{
    public array $slides = [];
    public int $active = 0;

    public function mount(array $slides = [])
    {
        $this->slides = collect($slides)
            ->filter(fn ($s) => !empty($s['media_type']))
            ->values()
            ->all();
    }

    public function goTo($i): void
    {
        $i = (int) $i;

        if ($i < 0 || $i >= count($this->slides)) return;

        $this->active = $i;
    }
    
    public function next(): void
    {
        $count = count($this->slides);
        if ($count === 0) return;
        $this->active = ($this->active + 1) % $count;
    }

    public function prev(): void
    {
        $count = count($this->slides);
        if ($count === 0) return;
        $this->active = ($this->active - 1 + $count) % $count;
    }

    public function render()
    {
        return view('livewire.home-slider');
    }
}
