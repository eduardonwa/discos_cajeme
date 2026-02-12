<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\DB;

class PriceReader
{
    public static function make(string $column = 'price', string $name = 'price_readonly'): Placeholder
    {
        return Placeholder::make($name)
            ->label($column === 'compare_at_price' ? 'Precio compare at' : 'Precio')
            ->content(function ($record) use ($column) {
                $cents = DB::table('product_variants')
                    ->where('id', $record->id)
                    ->value($column);

                if ($cents === null) return '—';

                $mxn = number_format(((int) $cents) / 100, 2);

                return "$mxn MXN";
            });
    }
}
