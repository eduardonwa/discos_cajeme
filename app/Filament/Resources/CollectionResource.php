<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\Collection;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\View;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\CollectionResource\Pages;
use App\Filament\Resources\CollectionResource\RelationManagers\ProductsRelationManager;

use function PHPSTORM_META\map;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Tienda';

    public static function getModelLabel(): string
    {
        return 'Colección';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Colecciones';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(12)->schema([
                // IZQUIERDA
                Grid::make()->columns(1)->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                    TextInput::make('slug')
                        ->label('Enlace')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Textarea::make('description')->maxLength(255),
                    Toggle::make('is_active')
                        ->label(fn (Get $get) => $get('is_active') ? 'Desactivar' : 'Activar')
                        ->live()
                        ->default(true)
                        ->required(),
                ])->columnSpan([
                    'default' => 12,
                    'md'      => 6,
                ]),
                // DERECHA
                Grid::make()->columns(1)->schema([
                    Select::make('featured_product_id')
                        ->label('Producto destacado')
                        ->relationship(
                            name: 'featuredProduct',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query, \Filament\Forms\Get $get)
                                => $query->whereHas('collections', fn ($q) => $q->where('collections.id', $get('id')))
                        )
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->nullable(),
                    View::make('filament/fields/collection-featured')
                        ->visible(fn (Get $get) => filled($get('featured_product_id')))
                        ->viewData(fn (Get $get) => [
                            'product' => Product::find($get('featured_product_id')),
                        ]),
                ])->columnSpan([
                    'default' => 12,
                    'md'      => 6,
                ])
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Enlace')
                    ->searchable(),
                TextColumn::make('is_active')
                    ->label('Estado')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        $isActive = $record->is_active;
                        return $isActive ? 'Activo' : 'Inactivo';
                    })
                    ->color(function ($record) {
                        $isActive = $record->is_active;
                        return $isActive ? 'success' : 'danger';
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
        ];
    }
}
