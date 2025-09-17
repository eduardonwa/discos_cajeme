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
        return Product::with(['media', 'variants']) // Asegúrate de cargar la relación
            ->whereHas('media')
            ->limit(100)
            ->get()
            ->map(function (Product $product) {
                $product->updateStockStatus();
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image_url' => $product->getFirstMediaUrl('featured', 'sm_thumb'),
                    'price' => FormatMoney::format($product->price),
                    'total_stock' => $product->total_stock,
                    'has_variants' => $product->has_variants,
                    'variants_count' => $product->variants->count(),
                    'stock_status' => $product->stock_status,
                    'stock_status_class' => $this->getStockStatusClass($product->stock_status),
                ];
            })
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