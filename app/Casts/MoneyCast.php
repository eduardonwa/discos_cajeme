<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Money\Currency;
use Money\Money;

class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // Si la columna es NULL (ej. compare_at_price), regresa NULL
        if ($value === null) {
            return null;
        }

        // En DB guardamos centavos (int). Money acepta int|string.
        return new Money((int) $value, new Currency('MXN'));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // Permite setear null (para columnas nullable)
        if ($value === null || $value === '') {
            return null;
        }

        // Si ya viene como Money, extrae centavos
        if ($value instanceof Money) {
            return (int) $value->getAmount();
        }

        // Si viene como int (centavos) o string numérico de centavos
        if (is_int($value) || ctype_digit((string) $value)) {
            return (int) $value;
        }

        // Si viene como string con pesos (p.ej. "4.99" o "$ 1,999.00")
        $normalized = str_replace([',', '$', ' '], '', (string) $value);
        if (is_numeric($normalized)) {
            return (int) round(((float) $normalized) * 100); // pesos -> centavos
        }

        throw new \InvalidArgumentException("Invalid money value for {$key}");
    }
}
