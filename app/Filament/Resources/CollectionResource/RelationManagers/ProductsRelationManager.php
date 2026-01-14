<?php

namespace App\Filament\Resources\CollectionResource\RelationManagers;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Fields\ProductSelector;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $modelLabel = 'Colección';

    public function getTableHeading(): string
    {
        return 'Productos en esta colección';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                ProductSelector::make('selected_products')
                    ->label('Seleccionar productos (máximo 10)')
                    ->rules(['array', 'max:10'])
                    ->afterStateHydrated(function (ProductSelector $component, $state) {
                        // convertir string JSON a array si es necesario
                        if (is_string($state)) {
                            $stat = json_decode($state, true) ?? [];
                        }
                        $component->state((array) $state); 
                    })
                    ->dehydrateStateUsing(fn ($state) => is_array($state) ? $state : [])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('media')
                    ->label('Imagen')
                    ->collection('featured')
                    ->size(50)
                    ->extraImgAttributes([
                        'style' => 'border-radius: 0.5rem;'
                    ]),
                TextColumn::make('name')
                    ->label('Nombre'),
                TextColumn::make('total_stock')
                    ->label('Inventario')
                    ->sortable(),
                TextColumn::make('published')
                    ->label('Estado')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Disponible' : 'Agotado')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('manage_products')
                    ->label('Gestionar productos')
                    ->form([
                        ProductSelector::make('selected_products')
                            ->label('Productos en esta colección')
                            ->default(fn () => $this->getOwnerRecord()->products->pluck('id')->toArray())
                            ->required()
                    ])
                    ->action(function (array $data) {
                        $productIds = is_array($data['selected_products']) 
                            ? $data['selected_products'] 
                            : json_decode($data['selected_products'], true) ?? [];
                        $this->getOwnerRecord()->products()->sync($productIds);
                    })
                    ->slideOver()
                    ->modalHeading('Agregar productos a esta colección'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Quitar')
                    ->hidden(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Quitar seleccionados')
            ]);
    }

    protected function handleRecordCreation(array $data): void
    {
        $productIds = $this->normalizeProductIds($data['selected_products'] ?? []);
        $this->getOwnerRecord()->products()->syncWithoutDetaching($productIds);
    }

    protected function handleRecordUpdate($record, array $data): void
    {
        $productIds = $this->normalizeProductIds($data['selected_products'] ?? []);
        $this->getOwnerRecord()->products()->syncWithoutDetaching($productIds);
    }

    private function normalizeProductIds($ids): array
    {
        if (is_string($ids)) {
            $ids = json_decode($ids, true) ?? [];
        }
        
        return array_filter((array) $ids, fn($id) => !empty($id));
    }    
}
