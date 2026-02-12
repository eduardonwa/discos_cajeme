<?php

namespace App\Helpers;

use Money\Money;

class FormatMoney
{
    public static function format($amount): string
    {
        if ($amount instanceof \Money\Money) {
            $value = $amount->getAmount() / 100;
        } else {
            $value = $amount / 100;
        }

        // Si es entero, no mostrar decimales
        if (fmod($value, 1) === 0.0) {
            return '$ ' . number_format($value, 0, '.', ',');
        }

        // Si tiene centavos reales, sí mostrar 2 decimales
        return '$ ' . number_format($value, 2, '.', ',');
    }
}