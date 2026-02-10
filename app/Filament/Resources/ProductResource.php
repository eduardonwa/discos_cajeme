<?php

namespace App\Filament\Resources;

use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Tienda';
    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return 'Producto';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Productos';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)
                ->schema([
                    // Columna izquierda: imágenes
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
                                ->label('Galería')
                                ->maxSize(1500)
                                ->collection('images')
                                ->multiple()
                                ->image()
                                ->columnSpanFull()
                                ->panelLayout('grid'),
                        ])
                        ->columnSpan([
                            'default' => 1,
                            'sm' => 12,
                            'md' => 8,
                            'lg' => 5,
                        ]),

                    // Columna derecha: tabs
                    Grid::make(1)
                        ->schema([
                            Tabs::make('Tabs')->tabs([
                                Tab::make('Información')->schema([
                                    TextInput::make('cover_img_alt')
                                        ->label('Cover alt')
                                        ->maxLength(255),

                                    TextInput::make('name')
                                        ->label('Nombre')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn ($state, $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                                    Textarea::make('description')
                                        ->label('Descripción')
                                        ->rows(4),

                                    Grid::make(2)->schema([
                                        Toggle::make('buy_now_enabled')
                                            ->label('Activar "Comprar ahora"')
                                            ->default(false)
                                            ->helperText('Se mostrará junto al botón de "Agregar al carrito"'),

                                        Toggle::make('published')
                                            ->label('Publicar en tienda')
                                            ->inline(true),
                                    ]),

                                    TextInput::make('slug')
                                        ->label('Slug')
                                        ->required()
                                        ->unique(ignoreRecord: true),
                                ]),

                                // ✅ Inventario: SOLO lectura desde variantes
                                Tab::make('Inventario')->schema([
                                    TextInput::make('computed_total_stock')
                                        ->label('Unidades (desde variantes)')
                                        ->numeric()
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->formatStateUsing(fn ($state, $record) => $record?->computed_total_stock ?? 0),
                                    Select::make('stock_status')
                                        ->label('Estado de inventario')
                                        ->options([
                                            'in_stock' => 'Disponible',
                                            'low_stock' => 'Últimas unidades',
                                            'sold_out' => 'Agotado',
                                        ])
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->formatStateUsing(function ($state, $record) {
                                            if (! $record) return 'in_stock';
                                            $total = $record->computed_total_stock;
                                            $low   = $record->low_stock_threshold ?? 5;

                                            return $total <= 0 ? 'sold_out' : ($total <= $low ? 'low_stock' : 'in_stock');
                                        }),
                                    TextInput::make('low_stock_threshold')
                                        ->label('Umbral para bajo stock')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(5),
                                ]),
                                Tab::make('Ofertas')->schema([
                                    TextInput::make('promo_label')
                                        ->label('Etiqueta promocional (opcional)')
                                        ->placeholder('REBAJA / LIQUIDACIÓN / -20%')
                                        ->maxLength(50),
                                ]),
                            ]),
                        ])
                        ->columnSpan([
                            'default' => 1,
                            'sm' => 12,
                            'md' => 8,
                            'lg' => 7,
                        ]),
                ])
                ->columns(12),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            SpatieMediaLibraryImageColumn::make('Imagen')
                ->collection('featured')
                ->size(50)
                ->extraImgAttributes(['style' => 'border-radius: 0.5rem;']),
            TextColumn::make('name')
                ->label('Nombre')
                ->sortable()
                ->searchable(),
            TextColumn::make('computed_total_stock')
                ->label('Inventario')
                ->sortable()
                ->state(fn ($record) => $record->computed_total_stock),
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

    public static function getRelations(): array
    {
        return [
            VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
