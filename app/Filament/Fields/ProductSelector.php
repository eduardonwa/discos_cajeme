<?php

namespace App\Filament\Fields;

use App\Models\Product;
use App\Helpers\FormatMoney;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Field;

class ProductSelector extends Field
{
    protected string $view = 'filament.fields.product-selector';

    protected function setUp(): void
    {
        parent::setUp();
    
        $this->afterStateHydrated(function (ProductSelector $component, $state) {
            $component->state($this->normalizeState($state));
        });
    
        $this->dehydrateStateUsing(fn ($state) => $this->normalizeState($state));
    }
    
    private function normalizeState($state): array
    {
        if (is_string($state)) {
            $state = json_decode($state, true) ?? [];
        }
        
        return array_values(array_filter((array) $state, fn($id) => is_numeric($id)));
    }

    public function getProducts(): array
    {
        return Product::with(['media', 'variants'])
            ->whereHas('media')
            ->limit(100)
            ->get()
            ->map(fn(Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'image_url' => $p->getFirstMediaUrl('featured', 'sm_thumb'),
                'price' => \App\Helpers\FormatMoney::format($p->price),
                'total_stock' => $p->computed_total_stock,
                'has_variants' => $p->has_variants,
                'variants_count' => $p->variants->count(),
                'stock_status' => $p->stock_status,
                'stock_status_class' => $this->getStockStatusClass($p->stock_status),
            ])
            ->toArray();
    }

    protected function getStockStatusClass(string $status): string
    {
        return match($status) {
            'in_stock' => 'text-green-500',
            'low_stock' => 'text-yellow-500',
            'sold_out' => 'text-red-500',
            default => 'text-gray-500',
        };
    }
}