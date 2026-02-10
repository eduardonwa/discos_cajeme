<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\Attribute;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $modelLabel = 'atributos';

    public function getTableHeading(): string
    {
        return 'Variaciones de producto';
    }

    public function form(Form $form): Form
    {
        $hydrate = function ($component, $state) {
            if ($state instanceof \Money\Money) $state = (int) $state->getAmount();
            $cents = (int) ($state ?? 0);
            $mxn = $cents / 100;

            $component->state(
                fmod($mxn, 1) == 0
                    ? number_format($mxn, 0, '.', ',')
                    : number_format($mxn, 2, '.', ',')
            );
        };

        $toCents = fn ($v) => (int) round(
            (float) str_replace([',', '$', ' '], '', (string) $v) * 100
        );

        $toPesosStr = fn ($v) => number_format(
            (float) str_replace([',', '$', ' '], '', (string) $v),
            2, '.', ','
        );

        $dehydrate = fn ($state) => $toCents($state);

        $hydrateCompare = function ($component, $state) use ($hydrate) {
            $amount = $state instanceof \Money\Money
                ? (int) $state->getAmount()
                : (int) str_replace([',', '$', ' '], '', (string) $state);

            if ($state === null || $state === '' || $amount <= 0) {
                $component->state(null);
                return;
            }

            $hydrate($component, $state);
        };

        // ✅ compare_at_price -> centavos o null
        $dehydrateCompare = function ($state, Get $get) use ($toCents) {
            if ($state === null || $state === '') return null;

            $compare = $toCents($state);
            $price   = $toCents($get('price'));

            if ($compare <= 0 || $compare <= $price) return null;

            return $compare;
        };

        return $form
            ->schema([
                Section::make('Información')
                    ->schema([
                        // Fila 1: Imagen
                        SpatieMediaLibraryFileUpload::make('media')
                            ->collection('product-variant-image')
                            ->label('Imagen de la variante')
                            ->image()
                            ->columnSpanFull(),
                        // Fila 2: Precios
                        Grid::make(2)->schema([
                            TextInput::make('price')
                                ->label('Precio (actual)')
                                ->inputMode('decimal')
                                ->mask(RawJs::make(<<<'JS'
                                    $input => {
                                        let v = $input.replace(/[^0-9.,]/g, '');
                                        let parts = v.split('.');
                                        if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
                                        if (parts[1]?.length > 2) parts[1] = parts[1].slice(0,2);
                                        return parts.join('.');
                                    }
                                JS))
                                ->afterStateHydrated($hydrate)
                                ->dehydrateStateUsing($dehydrate)
                                ->live(onBlur: false, debounce: 300)
                                ->required(),
                            TextInput::make('compare_at_price')
                                ->label('Precio referencia (tachado)')
                                ->inputMode('decimal')
                                ->mask(RawJs::make(<<<'JS'
                                    $input => {
                                        let v = $input.replace(/[^0-9.]/g, '');
                                        let parts = v.split('.');
                                        if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
                                        if (parts[1]?.length > 2) parts[1] = parts[1].slice(0,2);
                                        return parts.join('.');
                                    }
                                JS))
                                ->afterStateHydrated($hydrateCompare)
                                ->dehydrateStateUsing($dehydrateCompare)
                                ->hint(function ($get) use ($toCents) {
                                    $price   = $toCents($get('price'));
                                    $compare = $toCents($get('compare_at_price'));

                                    if ($price > 0 && $compare > $price) {
                                        $p = (int) round((1 - ($price / $compare)) * 100);
                                        return "Mostrará {$p}% de descuento";
                                    }
                                    return 'Dejar vacío si no hay oferta';
                                })
                                ->rule(function ($get) use ($toCents) {
                                    $price = $toCents($get('price'));
                                    return fn ($attr, $value, $fail) =>
                                        ($value !== null && $value !== '' && $toCents($value) <= $price)
                                            ? $fail('Debe ser mayor que el Precio para mostrar oferta.')
                                            : null;
                                }),
                            Actions::make([
                                Action::make('marcar_oferta')
                                    ->label('Usar precio actual como "antes"')
                                    ->action(function ($get, $set) use ($toPesosStr) {
                                        $set('compare_at_price', $toPesosStr($get('price')));
                                    }),
                            ])->columnSpanFull(),
                        ]),
                        // Fila 3: Stock + Estado
                        Grid::make(2)->schema([
                            TextInput::make('total_variant_stock')
                                ->label('Unidades')
                                ->required()
                                ->numeric()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('is_active', (int) $state > 0);
                                }),
                            Toggle::make('is_active')
                                ->label('Estado')
                                ->inline(false)
                                ->disabled(fn (callable $get) => (int) $get('total_variant_stock') <= 0),
                        ]),
                    ]),
                Repeater::make('attributes')
                    ->label('Grupo de atributos')
                    ->relationship('attributes')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('attribute_id')
                                    ->label('Nombre')
                                    ->relationship('attribute', 'key') // Relación con `attributes` (singular)
                                    ->searchable()
                                    ->createOptionForm([
                                        TextInput::make('key')
                                            ->label('Nuevo atributo')
                                            ->required(),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        // Crear una nueva clave en la tabla attributes
                                        return Attribute::create(['key' => $data['key']])->id;
                                    }),
                                TextInput::make('value')
                                    ->label('Valor')
                                    ->required(),
                           ])
                    ])
                    ->grid(2)
                    ->columnSpanFull()
                    ->collapsible()
                    ->reorderable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                SpatieMediaLibraryImageColumn::make('media')
                    ->label('Imagen')
                    ->collection('product-variant-image')
                    ->size(50)
                    ->extraImgAttributes([
                        'style' => 'border-radius: 0.5rem;'
                    ]),
                TextColumn::make('attributes.attribute.key')
                    ->label('Atributos')
                    ->searchable()
                    ->formatStateUsing(function ($record) {
                        // Obtener todas las combinaciones de key (de la tabla attributes) y value (de la tabla attribute_variants), luego unirlas en una sola línea
                        $record->load('attributes.attribute');
                        return $record->attributes->map(function ($attributeVariant) {
                            return "{$attributeVariant->attribute->key}: {$attributeVariant->value}";
                        })->join(', ') ?? 'No hay atributos';
                    }),
                TextColumn::make('total_variant_stock')
                    ->label('Inventario')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format(((int) $state) / 100, 2, '.', ',')),
                TextColumn::make('compare_at_price')
                    ->label('Antes')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => $state ? number_format(((int) $state) / 100, 2, '.', ',') : '—'),
                TextColumn::make('is_active')
                    ->label('Estado')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Activo' : 'Inactivo')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear variación'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }    
}
