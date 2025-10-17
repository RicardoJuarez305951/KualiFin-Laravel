<?php

namespace App\Enums;

enum CreditoEstado: string
{
    case ACTIVO = 'activo';
    case APROBADO = 'aprobado';
    case CANCELADO = 'cancelado';
    case DESEMBOLSADO = 'desembolsado';
    case LIQUIDADO = 'liquidado';
    case RECHAZADO = 'rechazado';
    case PROSPECTADO = 'prospectado';
    case PROSPECTADO_REACREDITO = 'prospectado_recredito';
    case SOLICITADO = 'solicitado';
    case SUPERVISADO = 'supervisado';
    case VENCIDO = 'vencido';
    case AVAL_RIESGO = 'aval_riesgo';
    case CLIENTE_RIESGO = 'cliente_riesgo';
    case CLIENTE_AVAL_RIESGO = 'cliente_aval_riesgo';
    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $estado): string => $estado->value, self::cases());
    }
}
