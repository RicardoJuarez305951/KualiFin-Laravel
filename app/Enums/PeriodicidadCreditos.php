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

    public static function default(): self
    {
        return self::TRECE;
    }

    public function weeks(): int
    {
        return match ($this) {
            self::CUATRO => 4,
            self::DOCE => 12,
            self::TRECE => 13,
            // "veintidos" se utiliza para representar el plan de 14 semanas histórico.
            self::VEINTIDOS => 14,
        };
    }

    public static function tryFromLabel(string $label): ?self
    {
        $enum = self::tryFrom($label);

        if ($enum !== null) {
            return $enum;
        }

        $normalized = strtolower(preg_replace('/\s+/', '', $label));

        return match ($normalized) {
            '4semanas', 'cuatrosemanas', 'semanal4' => self::CUATRO,
            '12semanas', 'docesemanas', 'semanal12' => self::DOCE,
            '13semanas', 'trecesemanas', 'semanal13' => self::TRECE,
            '14semanas', 'catorcesemanas', 'semanal14',
            '22semanas', 'veintidossemanas', 'semanal22' => self::VEINTIDOS,
            default => null,
        };
    }

    public static function resolveWeeks(?string $value): ?int
    {
        if (!$value) {
            return null;
        }

        $enum = self::tryFromLabel($value);

        if ($enum instanceof self) {
            return $enum->weeks();
        }

        if (preg_match('/(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
