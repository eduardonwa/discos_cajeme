<?php

namespace App\Filament\Pages;

use App\Models\Collection;
use App\Models\HomePage;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class HomePageEditor extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.homepage';
    protected static ?string $navigationLabel = 'Homepage';
    protected static ?string $title = 'Homepage';

    public ?array $data = [];

    public function mount(): void
    {
        $home = HomePage::firstOrCreate([]);
        $this->form->fill($home->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Hero')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('hero_media')
                            ->label('Imagen Hero')
                            ->collection('home_hero_img')
                            ->image(),

                        TextInput::make('hero_image_alt')
                            ->label('Alt')
                            ->maxLength(255),
                        
                        TextInput::make('hero_img_link')
                            ->label('Link')
                            ->maxLength(255)
                    ]),

                Section::make('Tab Collections')
                    ->schema([
                        Repeater::make('tab_collection_ids')
                            ->label('Colecciones tabs')
                            ->orderColumn()
                            ->defaultItems(0)
                            ->maxItems(4)
                            ->schema([
                                Select::make('id')
                                    ->label('Colección')
                                    ->options(Collection::query()->where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                            ])
                            // guardamos como array plano [1,2,3] en DB
                            ->dehydrateStateUsing(fn ($state) => collect($state ?? [])
                                ->pluck('id')->filter()->values()->all()
                            )
                            // cuando cargamos desde DB (array plano), lo convertimos a repeater items
                            ->afterStateHydrated(function (Forms\Components\Repeater $component, $state) {
                                if (is_array($state) && (empty($state) || is_int($state[0] ?? null))) {
                                    $component->state(collect($state)->map(fn ($id) => ['id' => $id])->all());
                                }
                            }),

                        TextInput::make('tab_products_limit')
                            ->label('Límite de productos por tab')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(18)
                            ->required()
                    ]),
                
                Section::make('CTA')
                    ->schema([
                        TextInput::make('cta_header')
                            ->label('Encabezado')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('cta_description')
                            ->label('Descripción')
                            ->rows(3),
                        SpatieMediaLibraryFileUpload::make('cta_media')
                            ->label('Imagen')
                            ->collection('home_cta_img')
                            ->image(),
                        TextInput::make('cta_bg_img_alt')
                            ->label('Alt')
                            ->maxLength(255),
                        TextInput::make('cta_button')
                            ->label('Texto del botón')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('cta_button_link')
                            ->label('Link del botón')
                            ->required()
                            ->maxLength(255)
                    ]),
                
                Section::make('Spotlight')
                    ->schema([
                        TextInput::make('spotlight_header')
                            ->label('Header eyebrow')
                            ->maxLength(255),
                        Select::make('spotlight_product_id')
                            ->label('Producto')
                            ->options(Product::query()->pluck('name', 'id'))
                            ->searchable(),
                        TextInput::make('spotlight_override_title')
                            ->label('Título (opcional)')
                            ->maxLength(255),
                        Textarea::make('spotlight_override_description')
                            ->label('Descripción (opcional)'),
                        SpatieMediaLibraryFileUpload::make('spotlight_override_media')
                            ->label('Imagen (opcional)')
                            ->collection('home_spotlight_override')
                            ->image()
                            ->helperText('Si no hay imagen, usa la featured del producto')
                    ]),

                Section::make('Colecciones')
                    ->schema([
                        TextInput::make('rail_collection_header')
                            ->label('Encabezado')
                            ->maxLength(255),
                        Textarea::make('rail_collection_description')
                            ->label('Descripción')
                            ->rows(2), 
                        Repeater::make('rail_collection_ids')
                            ->label('Colecciones')
                            ->orderColumn()
                            ->defaultItems(0)
                            ->maxItems(8)
                            ->schema([
                                Select::make('id')
                                    ->label('Colección')
                                    ->options(Collection::query()->where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                            ])
                            ->dehydrateStateUsing(fn ($state) => collect($state ?? [])
                                ->pluck('id')->filter()->values()->all()
                            )
                            ->afterStateHydrated(function (Forms\Components\Repeater $component, $state) {
                                if (is_array($state) && (empty($state) || is_int($state[0] ?? null))) {
                                    $component->state(collect($state)->map(fn ($id) => ['id' => $id])->all());
                                }
                            })
                    ])
            ]);
    }

    public function save(): void
    {
        $home = HomePage::firstOrCreate([]);
        $home->update($this->form->getState());

        Notification::make()
            ->title('Homepage guardada')
            ->success()
            ->send();
    }
}
