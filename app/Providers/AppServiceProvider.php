<?php

namespace App\Providers;

use Money\Money;
use App\Models\User;
use NumberFormatter;
use App\Models\Collection;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Laravel\Fortify\Fortify;
use App\Factories\CartFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Money\Currencies\ISOCurrencies;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Filament\Tables\Actions\EditAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Money\Formatter\IntlMoneyFormatter;
use Filament\Tables\Actions\CreateAction;
use App\Actions\Webshop\MigrateSessionCart;
use Laravel\Fortify\Http\Requests\LoginRequest;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        Cashier::calculateTaxes();
        
        // cuando un usuario no esta autenticado e ingresa, migrar el carrito de la sesion
        Fortify::authenticateUsing(function (Request $request) {
            /** @var LoginRequest $request */

            $email = $request->input('email');
            $password = $request->input('password');

            $user = User::where('email', $email)->first();
    
            if ($user && Hash::check($password, $user->password)) {
                (new MigrateSessionCart)->migrate(CartFactory::make(), $user->cart ?: $user->cart()->create([]));
                return $user;
            }
        });

        // Establece el locale para la aplicación
        app()->setLocale('es_MX');

        // Establece el locale para Carbon (manejo de fechas)
        Carbon::setLocale('es_MX');

        // Configura el locale para PHP (necesario para formatos de fecha)
        setlocale(LC_TIME, 'es_MX.utf8'); // Usa el locale instalado en el sistema

        // Configura el formateador de dinero
        Blade::stringable(function (Money $money) {
            $currencies = new ISOCurrencies();
            $numberFormatter = new NumberFormatter('es_MX', \NumberFormatter::CURRENCY);
            $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);
            
            // obtener el valor formateado
            $formattedValue = $moneyFormatter->format($money);

            // eliminar los dos ceros innecesarios si el valor es un número entero
            if (fmod($money->getAmount() / 100, 1) == 0) {
                $formattedValue = preg_replace('/\.00$/', '', $formattedValue);
            }

            return $formattedValue;
        });

        // slide over en vez de modal para crear nueva variante en ProductsResource
        CreateAction::configureUsing(function ($action) {
            return $action->slideOver();
        });

        EditAction::configureUsing(function ($action) {
            return $action->slideOver();
        });

        Blade::component('components.ui.icon', 'icon');

        View::composer('components.ui.collections-header', function ($view) {
            $view->with('productCollections', Collection::active()
                ->select('id', 'name', 'slug')
                ->get());
        });

        View::composer('components.ui.collections-filter', function ($view) {
            /** @var \App\Models\Collection|null $collection */
            $collection = $view->getData()['collection'] ?? null;
            if (!$collection) return;

            $rows = DB::table('attributes')
                ->join('attribute_variants', 'attributes.id', '=', 'attribute_variants.attribute_id')
                ->join('product_variants', 'product_variants.id', '=', 'attribute_variants.product_variant_id')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->join('collection_product', 'collection_product.product_id', '=', 'products.id')
                ->where('collection_product.collection_id', $collection->id)
                // opcional: solo disponibles
                ->where('products.stock_status', '!=', 'sold_out')
                ->groupBy('attributes.key', 'attribute_variants.value')
                ->orderBy('attributes.key')
                ->orderBy('attribute_variants.value')
                ->select([
                    'attributes.key',
                    'attribute_variants.value',
                    DB::raw('COUNT(DISTINCT product_variants.id) as count'),
                ])
                ->get();

                $facets = $rows->groupBy('key')->map(function ($group) {
                    return $group->map(fn ($r) => ['value' => $r->value, 'count' => (int)$r->count])->values();
                });

                $view->with('facets', $facets);
        });
    }
}