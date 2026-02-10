<?php

namespace App\Filament\Forms\Components;

use App\Models\Product;
use App\Models\Collection;
use Filament\Forms\Get;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;

class LinkPicker
{
    public static function schema(string $path = 'link'): array
    {
        return [
            Select::make("$path.type")
                ->label('Tipo de enlace')
                ->options([
                    'home' => 'Home (/)',
                    'url' => 'URL / Ruta',
                    'product' => 'Producto',
                    // todos los productos
                    'collections_all' => 'Todas las colecciones',
                    'collection' => 'Colección',
                ])
                ->default('home')
                ->live(),

            // PRODUCT
            Select::make("$path.value")
                ->label('Producto')
                ->visible(fn (Get $get) => $get("$path.type") === 'product')
                ->searchable()
                ->allowHtml()
                ->options(function (): array {
                    return Product::query()
                        ->where('published', true)
                        ->whereHas('variants', function ($q) {
                            $q->where('is_active', true)
                              ->where('total_variants_stock', '>', 0);
                        })
                        ->orderBy('name')
                        ->limit(10)
                        ->get()
                        ->mapWithKeys(function (Product $p) {
                            $thumb = $p->getFirstMediaUrl('featured', 'sm_thumb');
                            $img = $thumb
                                ? "<img src=\"{$thumb}\" alt=\"\" style=\"width:28px;height:28px;object-fit:contain;border-radius:6px;vertical-align:middle;margin-right:8px;\">"
                                : "<span style=\"display:inline-block;width:28px;height:28px;border-radius:6px;background:#eee;vertical-align:middle;margin-right:8px;\"></span>";

                            $label = "<span class=\"lp-option\">{$img}<span class=\"lp-title\">" . e($p->name) . "</span></span>";

                            return [$p->slug => $label];
                        })
                        ->all();
                })
                ->getSearchResultsUsing(function (string $search): array {
                    return Product::query()
                        ->where('published', true)
                        ->whereHas('variants', function ($q) {
                            $q->where('is_active', true)
                              ->where('total_variants_stock', '>', 0);
                        })
                        ->where(function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('slug', 'like', "%{$search}%");
                        })
                        ->limit(10)
                        ->get()
                        ->mapWithKeys(function (Product $p) {
                            $thumb = $p->getFirstMediaUrl('featured', 'sm_thumb');

                            $img = $thumb
                                ? "<img src=\"{$thumb}\" alt=\"\" style=\"width:28px;height:28px;object-fit:contain;border-radius:6px;vertical-align:middle;margin-right:8px;\">"
                                : "<span style=\"display:inline-block;width:28px;height:28px;border-radius:6px;background:#eee;vertical-align:middle;margin-right:8px;\"></span>";

                            $label = "<span class=\"lp-option\">{$img}<span class=\"lp-title\">" . e($p->name) . "</span></span>";

                            return [$p->slug => $label];
                        })
                        ->all();
                })
                ->getOptionLabelUsing(function ($value): ?string {
                    if (! $value) return null;

                    $p = Product::query()->where('slug', $value)->first();
                    if (! $p) return $value;

                    $thumb = $p->getFirstMediaUrl('featured', 'sm_thumb');
                    
                    $img = $thumb
                        ? "<img src=\"{$thumb}\" alt=\"\" style=\"width:28px;height:28px;object-fit:contain;border-radius:6px;vertical-align:middle;margin-right:8px;\">"
                        : "<span style=\"display:inline-block;width:28px;height:28px;border-radius:6px;background:#eee;vertical-align:middle;margin-right:8px;\"></span>";
                    
                    return "<span class=\"lp-option\">{$img}<span class=\"lp-title\">" . e($p->name) . "</span></span>";
                }),

            // COLLECTION
            Select::make("$path.value")
                ->label('Colección')
                ->visible(fn (Get $get) => $get("$path.type") === 'collection')
                ->searchable()
                ->getSearchResultsUsing(function (string $search): array {
                    return Collection::query()
                        ->where('is_active', true)
                        ->where(function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('slug', 'like', "%{$search}%");
                        })
                        ->limit(10)
                        ->pluck('name', 'slug')
                        ->all();
                })
                ->getOptionLabelUsing(function ($value): ?string {
                    if (! $value) return null;
                    
                    return Collection::query()
                        ->where('slug', $value)
                        ->value('name') ?? $value;
                }),

            // URL / RUTA
            TextInput::make("$path.value")
                ->label('URL o ruta')
                ->placeholder('Ej: /contact o https://...')
                ->visible(fn (Get $get) => $get("$path.type") === 'url')
                ->maxLength(2048),
        ];
    }
}
