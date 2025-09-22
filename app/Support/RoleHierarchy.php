<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class RoleHierarchy
{
    /**
     * Numerical weight per role. Higher value == higher privileges.
     */
    private const LEVELS = [
        'promotor' => 10,
        'supervisor' => 20,
        'ejecutivo' => 30,
        'administrativo' => 40,
        'superadmin' => 50,
    ];

    /**
     * Optional aliases that collapse to a known role key.
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'administrador' => 'administrativo',
        'admin' => 'administrativo',
    ];

    public static function normalize(?string $role): ?string
    {
        if ($role === null) {
            return null;
        }

        $role = strtolower($role);

        return self::ALIASES[$role] ?? $role;
    }

    public static function level(?string $role): int
    {
        $normalized = self::normalize($role);

        return $normalized ? (self::LEVELS[$normalized] ?? -1) : -1;
    }

    public static function resolvePrimaryRole(?Authenticatable $user): ?string
    {
        if (!$user || !method_exists($user, 'getRoleNames')) {
            return null;
        }

        $roles = $user->getRoleNames();
        if (!($roles instanceof Collection)) {
            $roles = collect($roles);
        }

        $normalized = $roles
            ->map(fn ($role) => self::normalize($role))
            ->filter();

        if ($normalized->isEmpty()) {
            return null;
        }

        return $normalized
            ->sortByDesc(fn ($role) => self::level($role))
            ->first();
    }

    public static function canAccess(?string $userRole, string $targetRole): bool
    {
        $userLevel = self::level($userRole);
        $targetLevel = self::level($targetRole);

        if ($targetLevel === -1) {
            return false;
        }

        return $userLevel >= $targetLevel;
    }

    public static function defaultSection(?string $userRole): string
    {
        $role = self::normalize($userRole) ?? 'promotor';

        return match ($role) {
            'administrativo', 'superadmin' => 'ejecutivo',
            'promotor', 'supervisor', 'ejecutivo' => $role,
            default => 'promotor',
        };
    }

    /**
     * @return list<string>
     */
    public static function sections(): array
    {
        return ['promotor', 'supervisor', 'ejecutivo'];
    }
}
