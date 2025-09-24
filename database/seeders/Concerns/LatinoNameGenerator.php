<?php

namespace Database\Seeders\Concerns;

class LatinoNameGenerator
{
    /**
     * @var array<int, string>
     */
    protected static array $firstNames = [
        'Álvaro',
        'Camila',
        'Valentina',
        'José Luis',
        'María José',
        'René',
        'Ana Sofía',
        'Miguel Ángel',
        'Ismael',
        'Montserrat',
        'Emiliano',
        'Frida',
        'Gael',
        'Ximena',
        'Óscar',
        'María Fernanda',
        'Diego',
        'Lucía',
        'Julián',
        'Paola',
        'Santiago',
        'Abril',
        'Iñaki',
        'Leticia',
        'Ángela',
        'Bruno',
        'Claudia',
        'Esteban',
        'Noemí',
        'Tomás',
    ];

    /**
     * @var array<int, string>
     */
    protected static array $lastNames = [
        'García',
        'Martínez',
        'López',
        'González',
        'Rodríguez',
        'Pérez',
        'Sánchez',
        'Ramírez',
        'Hernández',
        'Flores',
        'Muñoz',
        'Peña',
        'Castañeda',
        'Cortés',
        'Álvarez',
        'Núñez',
        'Benítez',
        'Medina',
        'Delgado',
        'Villalobos',
        'Quiñones',
        'Ibarra',
        'Saldaña',
        'Espíndola',
        'Maldonado',
        'Ortega',
        'Ríos',
        'Valdés',
        'Zúñiga',
        'Carranza',
    ];

    public static function firstName(): string
    {
        return static::$firstNames[array_rand(static::$firstNames)];
    }

    public static function lastName(): string
    {
        return static::$lastNames[array_rand(static::$lastNames)];
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function lastNames(): array
    {
        $apellidoPaterno = static::lastName();
        $apellidoMaterno = static::lastName();
        $attempts = 0;

        while ($apellidoMaterno === $apellidoPaterno && $attempts < 5) {
            $apellidoMaterno = static::lastName();
            $attempts++;
        }

        return [$apellidoPaterno, $apellidoMaterno];
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    public static function person(): array
    {
        [$apellidoPaterno, $apellidoMaterno] = static::lastNames();

        return [
            static::firstName(),
            $apellidoPaterno,
            $apellidoMaterno,
        ];
    }

    public static function fullName(): string
    {
        [$nombre, $apellidoPaterno, $apellidoMaterno] = static::person();

        return sprintf('%s %s %s', $nombre, $apellidoPaterno, $apellidoMaterno);
    }
}
