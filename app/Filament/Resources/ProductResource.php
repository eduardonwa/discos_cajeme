<?php

namespace App\Filament\Resources;

use Money\Money;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\ProductResource\Pages;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Tienda';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return 'Productos';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Productos';
    }

    public static function form(Form $form): Form
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

        $dehydrate = function ($state) {
            $state = str_replace([',', '$', ' '], '', (string) $state);
            return (int) round(((float) $state) * 100);
        };

        $toCents = fn ($v) => (int) round((float) str_replace([',', '$', ' '], '', (string) $v) * 100);

        $toPesosStr = fn ($v) => number_format(
            (float) str_replace([',', '$', ' '], '', (string) $v),
            2, '.', ','
        );

        // Hidrata compare_at_price: si viene 0/empty → null; si no, formatea como MXN
        $hydrateCompare = function ($component, $state) {
            if ($state instanceof \Money\Money) {
                $amount = (int) $state->getAmount();
            } else {
                $amount = (int) str_replace([',', '$', ' '], '', (string) $state);
            }

            if ($state === null || $state === '' || $amount <= 0) {
                $component->state(null);
                return;
            }

            // formateo tipo MXN (igual que tu $hydrate)
            $mxn = $amount / 100;
            $component->state(
                fmod($mxn, 1) == 0
                    ? number_format($mxn, 0, '.', ',')
                    : number_format($mxn, 2, '.', ',')
            );
        };

        // Deshidrata compare_at_price: nunca guardes 0/empty/<=price → null
        $dehydrateCompare = function ($state, \Filament\Forms\Get $get) {
            $raw = str_replace([',', '$', ' '], '', (string) $state);
            $compare = (int) round(((float) $raw) * 100);

            $rawPrice = str_replace([',', '$', ' '], '', (string) $get('price'));
            $price = (int) round(((float) $rawPrice) * 100);

            if ($state === null || $state === '' || $compare <= 0 || $compare <= $price) {
                return null;
            }

            return $state; // válido
        };

        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('featured_image')
                            ->label('Imagen destacada')
                            ->maxSize(3000)
                            ->collection('featured')
                            ->image()
                            ->required()
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('images')
                            ->label('Imagenes')
                            ->maxSize(1500)
                            ->collection('images')
                            ->multiple()
                            ->image()
                            ->extraAttributes(['class' => 'clase'])
                            ->columnSpanFull()
                            ->panelLayout('grid')
                    ])->columnSpan([
                        'default' => 1,
                        'sm' => 12,
                        'md' => 8,
                        'lg' => 5,
                    ]),
                Grid::make(1)
                    ->schema([
                        Tabs::make('Tabs')
                            ->tabs([
                                Tab::make('Información')
                                    ->schema([
                                        TextInput::make('cover_img_alt')
                                            ->label('Cover alt')
                                            ->maxLength(255),
                                        TextInput::make('name')
                                            ->label('Nombre')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->unique(ignoreRecord: true),
                                        Textarea::make('description')
                                            ->label('Descripción')
                                            ->rows(4),
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('buy_now_enabled')
                                                    ->label('Activar "Comprar ahora"')
                                                    ->default(false)
                                                    ->helperText('Se mostrará junto al botón de "Agregar al carrito"'),
                                                Toggle::make('published')
                                                    ->label('Publicar en tienda')
                                                    ->inline(true)
                                                    //->disabled(fn ($get) => $get('stock_status') === 'sold_out'),
                                            ])
                                    ]),
                                Tab::make('Inventario')
                                    ->schema([
                                        TextInput::make('total_product_stock')
                                            ->label('Unidades')
                                            ->numeric()
                                            ->disabled(fn ($get) => $get('has_variants'))
                                            ->reactive()
                                            ->formatStateUsing(function ($state, $record) {
                                                // si no tiene variantes, calcula la suma
                                                if ($record && $record->variants()->exists()) {
                                                    return $record->variants->sum('total_variant_stock');
                                                }
                                                return $state ?? 0;
                                            })
                                            ->dehydrated(fn ($get) => !$get('has_variants'))
                                            ->afterStateUpdated(function ($state, $set, $livewire) {
                                                $lowStockThreshold = $livewire->record->low_stock_threshold ?? 5;
                                                $newStatus = $state <= 0 ? 'sold_out' :
                                                            ($state <= $lowStockThreshold ? 'low_stock' : 'in_stock');
                                                $set('stock_status', $newStatus);
                                                if ($state > 0) {
                                                    $set('published', true);
                                                }
                                            }),
                                        Select::make('stock_status')
                                            ->label('Estado de inventario')
                                            ->options([
                                                'in_stock' => 'Disponible',
                                                'low_stock' => 'Últimas unidades',
                                                'sold_out' => 'Agotado',
                                            ])
                                            ->required()
                                            ->reactive(),
                                        TextInput::make('low_stock_threshold')
                                            ->label('Umbral para bajo stock')
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(5),
                                    ]),
                                Tab::make('Ofertas')
                                    ->schema([
                                        TextInput::make('price')
                                            ->label('Precio (actual)')
                                            ->inputMode('decimal') // ayuda al teclado móvil
                                            ->mask(RawJs::make(<<<'JS'
                                                $input => {
                                                    let v = $input.replace(/[^0-9.,]/g, '');
                                                    v = v.replace(/,/g, ','); // permite comas de miles si quisieras post-procesar
                                                    // normaliza: solo un punto decimal
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
                                            ->label('Precio de referencia (tachado)')
                                            ->reactive()
                                            ->live(onBlur: false, debounce: 300)
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
                                            ->afterStateHydrated($hydrateCompare)       // 👈 usar el específico
                                            ->dehydrateStateUsing($dehydrateCompare)    // 👈 usar el específico
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
                                        TextInput::make('promo_label')
                                            ->label('Etiqueta promocional (opcional)')
                                            ->placeholder('REBAJA / LIQUIDACIÓN / NUEVO')
                                            ->maxLength(50)
                                            ->suffixAction(
                                                Action::make('usarPorcentaje')
                                                    ->label('Usar -%')
                                                    ->icon('heroicon-o-percent-badge')
                                                    ->color('warning')
                                                    ->disabled(fn (Get $get) =>
                                                        blank($get('price')) ||
                                                        blank($get('compare_at_price')) ||
                                                        self::calcularDescuento($get) === null
                                                    )
                                                    ->tooltip('Usar porcentaje sugerido')
                                                    ->visible(fn (Get $get) => self::calcularDescuento($get) !== null)
                                                    ->action(function (Set $set, Get $get) {
                                                        $p = self::calcularDescuento($get);
                                                        if ($p !== null) {
                                                            $set('promo_label', "-{$p}%");
                                                        }
                                                    })
                                            )
                                            ->helperText(function (Get $get) {
                                                $p = self::calcularDescuento($get);
                                                return $p !== null
                                                    ? "Sugerencia: -{$p}% (basado en la comparación actual)"
                                                    : '';
                                            }),
                                        // Acción: copiar el price actual a compare_at_price
                                        Actions::make([
                                            Action::make('marcar_oferta')
                                                ->label('Usar precio actual como "antes"')
                                                ->action(function ($get, $set) use ($toPesosStr) {
                                                    // price esta en PESOS (string) en el estado de form
                                                    $set('compare_at_price', $toPesosStr($get('price')));
                                                })
                                            ]),
                                    ])
                            ]),
                ])->columnSpan([
                    'default' => 1,
                    'sm' => 12,
                    'md' => 8,
                    'lg' => 7,
                ]),
            ])->columns(12);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('Imagen')
                    ->collection('featured')
                    ->size(50)
                    ->extraImgAttributes([
                        'style' => 'border-radius: 0.5rem;'
                    ]),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_product_stock')
                    ->label('Inventario')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        // Si el producto tiene variantes, suma el stock desde la tabla pivote
                        if ($record->variants()->exists()) {
                            return $record->variants()->sum('product_variants.total_variant_stock');
                        }
                        // Si no tiene variantes, muestra el valor manual
                        return $state ?? 0;
                    }),
                TextColumn::make('variants_count')
                    ->label('Variaciones')
                    ->counts('variants')
                    ->sortable(),
                TextColumn::make('published')
                    ->label('Estado')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Activo' : 'Inactivo')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    protected static function calcularDescuento(Get $get): ?int
    {
        $toCents = fn ($v) => (int) round(((float) str_replace(',', '', (string) $v)) * 100);

        $price   = $toCents($get('price'));
        $compare = $toCents($get('compare_at_price'));

        if ($price > 0 && $compare > $price) {
            return (int) round((1 - ($price / $compare)) * 100);
        }

        return null;
    }
}
