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
    }
}