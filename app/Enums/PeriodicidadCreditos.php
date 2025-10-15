<?php

namespace App\Enums;

enum PeriodicidadCreditos: string
{
    case CUATRO = 'cuatro_semanas';
    case DOCE = 'doce_semanas';
    case TRECE = 'trece_semanas';
    case VEINTIDOS = 'veintidos_semanas';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $estado): string => $estado->value, self::cases());
    }
}
