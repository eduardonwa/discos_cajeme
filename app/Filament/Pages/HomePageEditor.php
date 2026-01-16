<?php

namespace App\Filament\Pages;

use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\HomePage;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Collection;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
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

        $data['tab_collections'] = $this->record->tab_collections ?? [];

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
        $state['tab_collections'] = collect($state['tab_collections'] ?? [])
            ->map(function ($item) {
                return [
                    'collection_id' => isset($item['collection_id']) ? (int) $item['collection_id'] : null,
                    'product_ids' => collect($item['product_ids'] ?? [])
                        ->filter()
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values()
                        ->all()
                ];
            })
            ->filter(fn ($item) => filled($item['collection_id']))
            ->values()
            ->all();

        $state['rail_collection_ids'] = collect($state['rail_collection_ids'] ?? [])
            ->pluck('id')
            ->filter()
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();
        
        // dd($state);

        $this->record->update($state);

        // Spatie: persistir uploads del form
        $this->form->model($this->record)->saveRelationships();

        Notification::make()
            ->title('Homepage guardada')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar')
                ->action('save')
        ];
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
                        ->content('Hero slides')
                        ->label(false)
                        ->columnSpan(12)
                        ->extraAttributes([
                            'class' => 'font-bold',
                            'style' => 'font-size: 2.2rem;'
                        ]),
                    Grid::make([
                        'default' => 1,
                        'md' => 2
                    ])
                    ->columnSpan(12)
                    ->schema([
                        Section::make('Slide 1')
                            ->columnSpan(1)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('hero_1_image')
                                    ->label(false)
                                    ->collection('hero_1')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(4096),

                                TextInput::make('hero_1_link')
                                    ->label('Enlace')
                                    ->nullable(),
                            ]),
                        Section::make('Slide 2')
                            ->columnSpan(1)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('hero_2_image')
                                    ->label(false)
                                    ->collection('hero_2')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(4096),

                                TextInput::make('hero_2_link')
                                    ->label('Enlace')
                                    ->nullable(),
                            ]),
                        Section::make('Slide 3')
                            ->columnSpan(1)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('hero_3_image')
                                    ->label(false)
                                    ->collection('hero_3')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(4096),

                                TextInput::make('hero_3_link')
                                    ->label('Enlace')
                                    ->nullable(),
                            ]),
                        Section::make('Slide 4')
                            ->columnSpan(1)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('hero_4_image')
                                    ->label(false)
                                    ->collection('hero_4')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(4096),

                                TextInput::make('hero_4_link')
                                    ->label('Enlace')
                                    ->nullable(),
                            ]),
                    ])
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
                        Repeater::make('tab_collections')
                            ->label('Colecciones')
                            ->defaultItems(0)
                            ->maxItems(4)
                            ->grid([
                                'default' => 1,
                                'md' => 2,
                                'lg' => 4
                            ])
                            ->columnSpan(12)
                            ->schema([
                                Select::make('collection_id')
                                    ->label('Colección seleccionada')
                                    ->options(fn () => Collection::query()
                                        ->where('is_active', true)
                                        ->pluck('name', 'id')
                                        ->toArray())
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, Set $set) => $set('product_ids', []))
                                    ->rules([
                                        fn (Get $get) => function ($attribute, $value, $fail) use ($get) {
                                            $ids = collect($get('../../tab_collections') ?? [])
                                                ->pluck('collection_id')
                                                ->filter()
                                                ->map(fn ($v) => (int) $v);

                                            if ($ids->count() !== $ids->unique()->count()) {
                                                $fail('No repitas colecciones.');
                                            }
                                        },
                                    ]),
                                Select::make('product_ids')
                                    ->label('Selección de productos')
                                    ->multiple()
                                    ->searchable()
                                    ->disabled(fn (Get $get) => blank($get('collection_id')))
                                    ->helperText(function (Get $get) {
                                        $limit = (int) ($get('../../../tab_products_limit') ?? 18);
                                        $count = count($get('product_ids') ?? []);
                                        return "Máximo: {$limit} — seleccionados: {$count}";
                                    })
                                    // llena el dropdown al abrir (sin teclear)
                                    ->options(function (Get $get) {
                                        $collectionId = $get('collection_id');
                                        if (! $collectionId) return [];

                                        return Product::query()
                                            ->whereHas('collections', fn ($q) => $q->whereKey($collectionId))
                                            ->orderByDesc('id')
                                            ->limit(25)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    // búsqueda global (cuando escribes)
                                    ->getSearchResultsUsing(function (string $search, Get $get) {
                                        $collectionId = $get('collection_id');
                                        if (! $collectionId) return [];
                                        return Product::query()
                                            ->whereHas('collections', fn ($q) => $q->whereKey($collectionId))
                                            ->where('name', 'like', "%{$search}%")
                                            ->orderBy('name')
                                            ->limit(50)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    // para que los ids ya elegidos siempre muestren su label
                                    ->getOptionLabelUsing(fn ($value) => Product::query()->whereKey($value)->value('name'))
                                    ->rules([
                                        fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            $limit = (int) ($get('../../../tab_products_limit') ?? 18);
                                            $count = is_array($value) ? count($value) : 0;

                                            if ($count > $limit) {
                                                $fail("Máximo {$limit} productos por pestaña.");
                                            }
                                        },
                                    ])

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
                                                ->options(
                                                    Product::query()
                                                        ->latest()
                                                        ->limit(21)
                                                        ->pluck('name', 'id')
                                                        ->toArray()
                                                    )
                                                ->searchable()
                                                ->live()
                                                ->getSearchResultsUsing(function (string $search) {
                                                    return Product::query()
                                                        ->where('name', 'like', "%{$search}%")
                                                        ->orderByDesc('id')
                                                        ->limit(55)
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                })
                                                ->getOptionLabelUsing(fn ($value) => Product::query()->whereKey($value)->value('name'))
                                                ->columnSpan(8),
                                            Placeholder::make('spotlight_product_preview')
                                                ->label('Preview')
                                                ->content(function (Get $get) {
                                                    $id = $get('spotlight_product_id');
                                                    if (! $id) return 'Sin producto seleccionado.';

                                                    $product = Product::find($id);
                                                    $url = $product?->getFirstMediaUrl('featured');
                                                    
                                                    if (! $product) return 'Producto no encontrado.';
                                                    if (! $url) return "“{$product->name}” no tiene featured.";
                                                    
                                                    return new HtmlString("
                                                        <div style='display:flex;gap:10px;align-items:center;'>
                                                            <img src='{$url}' style='width:89px;height:89px;object-fit:cover;border-radius:10px;' />
                                                            <div style='display:flex;flex-direction:column;'>
                                                                <p style='font-weight:600;'>Alt:</p>
                                                                <div style='font-size:15px;opacity:.7;'>{$product->cover_img_alt}</div>
                                                            </div>
                                                        </div>
                                                    ");
                                                })
                                                ->columnSpan(4)
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
