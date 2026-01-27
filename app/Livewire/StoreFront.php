<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\HomePage;
use App\Models\Collection;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class StoreFront extends Component
{
    use WithPagination;

    #[Url]
    public array $heroSlider = [];
    public $collections = [];
    public string $activeTab = '';
    
    public function mount()
    {
        $home = HomePage::first();

        $collectionLimit = (int) ($home->tab_products_limit ?? 10);

        $this->collections = collect($home->tab_collections ?? [])
            ->map(function ($block) use ($collectionLimit) {
                return $this->getCollectionTab(
                    (int) $block['collection_id'],
                    $block['product_ids'] ?? [],
                    $collectionLimit    
                );
            })
            ->filter()
            ->values()
            ->all();

        if ($this->activeTab === '' && ! empty($this->collections)) {
            $this->activeTab = $this->collections[0]->slug;
        }

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

    private function getCollectionTab(int $collectionId, array $productIds, int $collectionLimit)
    {
        $collection = Collection::query()
            ->active()
            ->find($collectionId);

        if (! $collection) return null;

        // si filament seleccionó productos específicos
        if (! empty($productIds)) {
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->where('published', true)
                ->with(['media', 'collections'])
                ->get()
                ->sortBy(fn ($p) => array_search($p->id, $productIds))
                ->values();
        } else {
            $products = Product::query()
                ->whereHas('collections', fn ($q) => $q->whereKey($collectionId))
                ->where('published', true)
                ->with(['media', 'collections'])
                ->orderByDesc('products.created_at')
                ->limit($collectionLimit)
                ->get();
        }

        $collection->setRelation('products', $products);

        return $collection;
    }

    public function setActiveTab(string $slug)
    {
        $this->activeTab = $slug;
    }

    public function render()
    {
        return view('livewire.store-front');
    }
}
