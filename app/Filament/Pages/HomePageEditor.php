<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\HomePage;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Collection;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class HomePageEditor extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.homepage';
    protected static ?string $navigationLabel = 'Homepage';
    protected static ?string $title = 'Homepage';
    public ?array $data = [];

    public HomePage $record;

    public function mount(): void
    {
        $this->record = HomePage::firstOrCreate([]);

        $data = $this->record->toArray();
        // tabs: [1,2,3] -> [['id'=>1],['id'=>2],...]
        $data['tab_collection_ids'] = collect($this->record->tab_collection_ids ?? [])
            ->map(fn ($id) => ['id' => (int) $id])
            ->values()
            ->all();

        // rail: [1,2,3] -> [['id'=>1],['id'=>2],...]
        $data['rail_collection_ids'] = collect($this->record->rail_collection_ids ?? [])
            ->map(fn ($id) => ['id' => (int) $id])
            ->values()
            ->all();

        // IMPORTANTE: ligar el form al record para Spatie uploads
        $this->form->model($this->record)->fill($data);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        // repeater -> array plano
        $state['tab_collection_ids'] = collect($state['tab_collection_ids'] ?? [])
            ->pluck('id')
            ->filter()
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();

        $state['rail_collection_ids'] = collect($state['rail_collection_ids'] ?? [])
            ->pluck('id')
            ->filter()
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();

        $this->record->update($state);

        // Spatie: persistir uploads del form
        $this->form->model($this->record)->saveRelationships();

        Notification::make()
            ->title('Homepage guardada')
            ->success()
            ->send();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->model($this->record)
            ->statePath('data')
            ->schema([
                // 1) HERO
                Grid::make(12)->schema([
                    Placeholder::make('hero_heading')
                        ->content('Hero')
                        ->label(false)
                        ->columnSpan(12)
                        ->extraAttributes([
                            'class' => 'font-bold',
                            'style' => 'font-size: 2.2rem;'
                        ]),
                    SpatieMediaLibraryFileUpload::make('hero_media')
                        ->label('Imagen Hero')
                        ->collection('home_hero_img')
                        ->image()
                        ->columnSpan([
                            'default' => 12,
                            'lg' => 6,
                        ]),

                    Grid::make(1)
                        ->columnSpan([
                            'default' => 12,
                            'lg' => 6,
                        ])
                        ->schema([
                            TextInput::make('hero_image_alt')
                                ->label('Alt')
                                ->maxLength(255),

                            TextInput::make('hero_img_link')
                                ->label('Link')
                                ->url()
                                ->maxLength(255),
                        ])
                ])
                ->extraAttributes([
                    'style' => '
                        margin: 2rem 0 4rem 0;
                        padding-bottom: 4rem;
                        border-bottom: 1px solid #ececec71;
                    '
                ]),

                // COLECCIONES TAB
                Grid::make(12)->schema([
                    Placeholder::make('Colecciones tab')
                        ->content('Colecciones tab')
                        ->label(false)
                        ->columnSpan(12)
                        ->extraAttributes([
                            'class' => 'font-bold',
                            'style' => 'font-size: 2.2rem;'
                        ]),
                    Grid::make(4)
                        ->columnSpan(12)
                        ->schema([
                            TextInput::make('tab_collection_header')
                                ->label('Encabezado')
                                ->maxLength(255)
                                ->columnSpan(3),
                            TextInput::make('tab_products_limit')
                                ->label('Límite de productos por tab')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(18)
                                ->required()
                                ->columnSpan(1),
                        ]),
                        Repeater::make('tab_collection_ids')
                            ->label('Colecciones')
                            ->defaultItems(0)
                            ->maxItems(4)
                            ->grid([
                                'default' => 1,
                                'md' => 2,
                                'lg' => 4,
                            ])
                            ->columnSpan(12)
                            ->schema([
                                Select::make('id')
                                    ->label('Colección')
                                    ->options(fn () => Collection::query()
                                        ->where('is_active', true)
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->distinct()
                                    ->required(),
                            ])
                ])
                ->extraAttributes([
                    'style' => '
                        margin: 2rem 0 4rem 0;
                        padding-bottom: 4rem;
                        border-bottom: 1px solid #ececec71;
                    '
                ]),
                
                // 3) CTA
                Grid::make(12)->schema([
                    Placeholder::make('cta_heading')
                        ->content('CTA')
                        ->label(false)
                        ->columnSpan(12)
                        ->extraAttributes([
                            'class' => 'font-bold',
                            'style' => 'font-size: 2.2rem;'
                        ]),
                    Grid::make(1)
                        ->columnSpan([
                            'default' => 12,
                            'lg' => 6
                        ])
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('cta_media')
                                ->label('Imagen')
                                ->collection('home_cta_img')
                                ->image(),
                            TextInput::make('cta_bg_img_alt')
                                ->label('Alt')
                                ->maxLength(255),
                        ]),

                    Grid::make(1)
                        ->columnSpan([
                            'default' => 12,
                            'lg' => 6,
                        ])
                        ->schema([
                            Tabs::make('CTA')
                                ->tabs([
                                    Tab::make('Copy')
                                        ->schema([
                                            TextInput::make('cta_header')
                                                ->label('Encabezado')
                                                ->required()
                                                ->maxLength(255),
                                            Textarea::make('cta_description')
                                                ->label('Descripción')
                                                ->rows(3),
                                        ]),
                                    Tab::make('Botón')
                                        ->schema([
                                            TextInput::make('cta_button')
                                                ->label('Texto del botón')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('cta_button_link')
                                                ->label('Link del botón')
                                                ->url()
                                                ->required()
                                                ->maxLength(255),
                                        ])
                                ])                            
                        ]),
                ])
                ->extraAttributes([
                    'style' => '
                        margin: 2rem 0 4rem 0;
                        padding-bottom: 4rem;
                        border-bottom: 1px solid #ececec71;
                    '
                ]),

                // 4) SPOTLIGHT
                Grid::make(12)->schema([
                    Placeholder::make('Spotlight')
                        ->content('Spotlight')
                        ->label(false)
                        ->columnSpan(12)
                        ->extraAttributes([
                            'class' => 'font-bold',
                            'style' => 'font-size: 2.2rem'
                        ]),
                    Grid::make(1)
                        ->columnSpan([
                            'default' => 12,
                            'lg' => 6
                        ])
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('spotlight_override_media')
                                ->label('Imagen (opcional)')
                                ->collection('home_spotlight_override')
                                ->image()
                                ->helperText('Si no hay imagen, usa la featured del producto')
                        ]),
                    Grid::make(1)
                        ->columnSpan([
                            'default' => 12,
                            'lg' => 6
                        ])
                        ->schema([
                            Tabs::make('Spotlight')
                                ->tabs([
                                    Tab::make('Producto')
                                        ->schema([
                                            Select::make('spotlight_product_id')
                                                ->label('Producto')
                                                ->options(Product::query()->pluck('name', 'id'))
                                                ->searchable(),
                                        ]),
                                    Tab::make('Copy')
                                        ->schema([
                                            TextInput::make('spotlight_header')
                                                ->label('Header eyebrow')
                                                ->maxLength(255),

                                            TextInput::make('spotlight_override_title')
                                                ->label('Título (opcional)')
                                                ->maxLength(255),

                                            Textarea::make('spotlight_override_description')
                                                ->label('Descripción (opcional)')
                                                ->rows(4),
                                        ]),
                                ])->columnSpanFull()
                        ])
                ])
                ->extraAttributes([
                    'style' => '
                        margin: 2rem 0 4rem 0;
                        padding-bottom: 4rem;
                        border-bottom: 1px solid #ececec71;
                    '
                ]),

                // 5) COLECCIONES RIEL
                Grid::make(12)->schema([
                    // Header tipo sección
                    Placeholder::make('rail_collections_heading')
                        ->content('Colecciones Riel')
                        ->label(false)
                        ->columnSpan(12)
                        ->extraAttributes([
                            'class' => 'font-bold mb-4 mt-6',
                            'style' => 'font-size: 2.2rem;',
                        ]),

                    // Campos superiores (copy)
                    Grid::make(12)
                        ->columnSpan(12)
                        ->schema([
                            TextInput::make('rail_collection_header')
                                ->label('Encabezado')
                                ->maxLength(255)
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 6,
                                ]),

                            Textarea::make('rail_collection_description')
                                ->label('Descripción')
                                ->rows(2)
                                ->columnSpan([
                                    'default' => 12,
                                    'md' => 6,
                                ]),
                        ]),

                    // Repeater horizontal
                    Repeater::make('rail_collection_ids')
                        ->label('Colecciones')
                        ->defaultItems(0)
                        ->maxItems(8)
                        ->grid([
                            'default' => 1,
                            'md' => 2,
                            'lg' => 4,
                        ])
                        ->columnSpan(12)
                        ->schema([
                            Select::make('id')
                                ->label('Colección')
                                ->options(fn () => Collection::query()
                                    ->where('is_active', true)
                                    ->pluck('name', 'id'))
                                ->searchable()
                                ->distinct()
                                ->required(),
                        ]),
                ])
                ->extraAttributes([
                    'style' => '
                        margin: 2rem 0 4rem 0;
                        padding-bottom: 4rem;
                        border-bottom: 1px solid #ececec71;
                    '
                ]),
            ]);
    }
}
