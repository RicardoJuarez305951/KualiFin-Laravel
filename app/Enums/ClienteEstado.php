<?php

namespace App\Enums;

enum ClienteEstado: string
{
    case ACTIVO = 'activo';
    case INACTIVO = 'inactivo';
    case MOROSO = 'moroso';
    // case DEUDOR = 'deudor';
    case REGULARIZADO = 'regularizado';
    case DESEMBOLSADO = 'desembolsado';
    case SUPERVISADO = 'supervisado';
    case VIGENTE = 'vigente';
    case DEMANDA = 'demanda';
    case VENCIDO = 'vencido';
    case FALLA = 'falla';
    case VENTA_REGISTRADA = 'venta_registrada';
    case POR_SUPERVISAR = 'por_supervisar';
    case DOBLE_CHECK = 'doble_check';
    case PROSPECTO = 'prospecto';
    case CANCELADO = 'cancelado';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $estado): string => $estado->value, self::cases());
    }
}
