<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use Filament\Pages\Auth\EditProfile;
use Illuminate\Support\Facades\Vite;
use App\Http\Middleware\CheckAdminRole;
use Awcodes\FilamentQuickCreate\QuickCreatePlugin;
use Awcodes\Overlook\OverlookPlugin;
use Awcodes\Overlook\Widgets\OverlookWidget;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {        
        $cssPath = Vite::asset('resources/css/app.css');

        FilamentAsset::register([
            Css::make('custom-stylesheet', $cssPath),
        ]);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->spa()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                CheckAdminRole::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                QuickCreatePlugin::make()
                    ->excludes([
                        \App\Filament\Resources\OrderResource::class
                    ]),
                OverlookPlugin::make()
                    ->sort(2)
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 3,
                        'lg' => 4,
                        'xl' => 5,
                        '2xl' => null
                    ])
            ])
            ->widgets([
                OverlookWidget::class
            ])
            ->navigationGroups([
                NavigationGroup::make('Productos')
                    ->label('Tienda')
                    ->icon('heroicon-o-shopping-cart'),
                NavigationGroup::make('Ventas')
                    ->label('Ventas')
                    ->icon('heroicon-o-presentation-chart-line')
            ])
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                'profile' => MenuItem::make()->label('Editar perfil'),
                MenuItem::make()
                    ->label('Ir al sitio')
                    ->url('/')
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-home'),
            ])
            ->profile(
                EditProfile::class,
                isSimple: false, // editar perfil con la navegacion por un lado
            )
            ->unsavedChangesAlerts(); // alerta de cambios sin guardar
    }
}
