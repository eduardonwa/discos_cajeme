<?php

namespace App\Livewire;

use App\Models\Collection;
use App\Models\HomePage;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class StoreFront extends Component
{
    use WithPagination;

    #[Url]
    public array $heroSlider = [];

    public function mount()
    {
        $home = HomePage::first();

        $this->heroSlider = [];

        if (!$home) return;

        $this->heroSlider = collect([
            ['collection' => 'hero_1', 'link' => $home->hero_1_link],
            ['collection' => 'hero_2', 'link' => $home->hero_2_link],
            ['collection' => 'hero_3', 'link' => $home->hero_3_link],
            ['collection' => 'hero_4', 'link' => $home->hero_4_link],
        ])->map(function ($slot) use ($home) {
            $sm = $home->getFirstMediaUrl($slot['collection'], 'hero_sm');
            $md = $home->getFirstMediaUrl($slot['collection'], 'hero_md');
            $lg = $home->getFirstMediaUrl($slot['collection'], 'hero_lg');

            // Si no hay al menos una, descartamos el slide
            $fallback = $lg ?: ($md ?: $sm);
            if (!$fallback) return null;

            $srcset = collect([
                $sm ? "{$sm} 900w" : null,
                $md ? "{$md} 1400w" : null,
                $lg ? "{$lg} 2400w" : null,
            ])->filter()->implode(', ');

            return [
                'src'       => $fallback,
                'srcset'    => $srcset,
                'sizes'     => '100vw',
                'link'      => $slot['link'] ?: null,
                'image_alt' => '',
            ];
        })->filter()->values()->all();
    }

    private function getCollectionWithProducts(string $slug, int $limit)
    {
        return Collection::query()
            ->active()
            ->where('slug', $slug)
            ->with(['products' => function ($q) use ($limit) {
                $q->where('published', true)
                  ->with(['media','collections'])
                  ->orderByDesc('products.created_at')
                  ->limit($limit);
            }])
            ->first();
    }

    public function render()
    {
        // Cada colección por separado (sueltas)
        $verano     = $this->getCollectionWithProducts('verano-libre-40', 4);
        $onSale     = $this->getCollectionWithProducts('on-sale', 4);
        $atemporal  = $this->getCollectionWithProducts('atemporal', 6);
        $comodidad  = $this->getCollectionWithProducts('comodidad-moderna', 6);

        return view('livewire.store-front', compact('verano','onSale','atemporal','comodidad'));
    }
}
