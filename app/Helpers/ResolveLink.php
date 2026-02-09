<?php

namespace App\Helpers;

class ResolveLink
{
    public static function href(array|string|null $link): string
    {
        // 1) Legacy: string directo
        if (is_string($link)) {
            return $link;
        }

        // 2) Null / vacío
        if (! is_array($link)) {
            return '#';
        }

        // 3) Nuevo formato (LinkPicker)
        return match ($link['type'] ?? null) {
            'home' => '/',
            'collections_all' => '/collections/all',
            'product' => !empty($link['value']) ? '/product/' . $link['value'] : '#',
            'collection' => !empty($link['value']) ? '/collections/' . $link['value'] : '#',
            'url' => $link['value'] ?? '#',
            default => '#',
        };
    }
}
