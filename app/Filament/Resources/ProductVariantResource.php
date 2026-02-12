<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\PriceReader;
use App\Filament\Resources\ProductVariantResource\Pages;
use App\Models\Attribute;
use App\Models\ProductVariant;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;

    protected static ?string $navigationGroup = 'Tienda';

    protected static ?string $navigationParentItem = 'Productos';
    
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return 'Variaciones';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Variaciones de productos';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select([
                'id',
                'product_id',
                'total_variant_stock',
                'is_active',
                'title',
                'is_default'
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('product-variant-image')
                                    ->label('Imagen')
                                    ->collection('product-variant-image')
                                    ->image()
                            ])->columnSpan(1),
                        Grid::make(1)
                            ->schema([
                                Group::make([
                                    PriceReader::make('price', 'price_readonly')->label('Precio'),
                                    PriceReader::make('compare_at_price', 'compare_at_price_readonly')->label('Precio "compare at"'),
                                ])->columns(2),
                                Placeholder::make('name')
                                    ->label('Producto')
                                    ->content(function ($record) {
                                        return $record->product->name; // desde la relación de "product" iteramos y obtenemos el nombre de los productos
                                    }),
                                Actions::make([
                                    Action::make('goToProduct')
                                        ->label('Ir al producto')
                                        ->color('info')
                                        ->icon('heroicon-o-arrow-top-right-on-square')
                                        ->url(fn ($record) => route(
                                            'filament.admin.resources.products.edit',
                                            $record->product->slug
                                        ))
                                        ->openUrlInNewTab(),
                                ])->columns(1),
                                KeyValue::make('attributes')
                                    ->label('Atributos')
                                    ->keyLabel('Nombre')
                                    ->valueLabel('Valor')
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function ($component, $record) {
                                        // Convertir los atributos existentes a un array clave-valor
                                        $attributes = $record->attributes->pluck('value', 'attribute.key')->toArray();
                                        $component->state($attributes);
                                    })
                                    ->afterStateUpdated(function ($state, $record) {
                                        // Guardar los atributos en la tabla attribute_variants
                                        $record->attributes()->delete(); // Eliminar atributos existentes
                                        foreach ($state as $key => $value) {
                                            // Buscar o crear el atributo en la tabla attributes
                                            $attribute = Attribute::firstOrCreate(['key' => $key]);
                                            // Crear el registro en attribute_variants
                                            $record->attributes()->create([
                                                'attribute_id' => $attribute->id,
                                                'value' => $value,
                                            ]);
                                        }
                                    }),
                                TextInput::make('total_variant_stock')
                                    ->label('Unidades')
                                    ->required()
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // si el stock es 0, 'is_active' será false
                                        $set('is_active', $state != 0);
                                    }),
                                Toggle::make('is_active')
                                    ->label('Estado')
                                    ->inline(false)
                                    ->disabled(fn (callable $get) => $get('total_variant_stock') == 0),
                            ])->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('media')
                    ->label('Imagen')
                    ->collection('product-variant-image')
                    ->size(50)
                    ->extraImgAttributes([
                        'style' => 'border-radius: 0.5rem;'
                    ]),
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('attributes')
                    ->label('Atributos')
                    ->formatStateUsing(function ($record) { // obtiene todos los atributos y valor de la colección "attributes"
                        return $record->attributes
                            ->map(fn ($attributeVariant) => $attributeVariant->attribute->key . ': ' . $attributeVariant->value)
                            ->join(', ');
                    }),
                TextColumn::make('total_variant_stock')
                    ->label('Inventario')
                    ->sortable(),
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
            ->actions([
                EditAction::make()->slideOver(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductVariants::route('/'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }
}