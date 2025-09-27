<?php

namespace App\View\Components\UI;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductGallery extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public array $featured,
        public array $images = [],
        public string $name = '',
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.ui.product-gallery');
    }
}
