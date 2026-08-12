<?php

namespace App\Modules\Auth\Enums;

enum RoleName: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Support = 'support';
    case Sales = 'sales';
    case ContentManager = 'content_manager';
    case Accountant = 'accountant';

    /**
     * Authority ladder for the RBAC hierarchy. A higher number outranks a lower
     * one; an actor may only manage managers below its own rank and may only
     * assign roles at or below its own rank. Custom, dashboard-created roles are
     * unknown to this enum and rank 0 (see rankFor()).
     */
    public function rank(): int
    {
        return match ($this) {
            self::SuperAdmin => 100,
            self::Admin => 90,
            self::ContentManager, self::Accountant => 50,
            self::Sales, self::Support => 30,
        };
    }

    /**
     * Rank for a stored role name, defaulting unknown/custom roles to 0.
     */
    public static function rankFor(string $name): int
    {
        return self::tryFrom($name)?->rank() ?? 0;
    }

    /**
     * Core roles are the two that grant full access and must never be edited,
     * deleted, or renamed from the dashboard.
     */
    public function isCore(): bool
    {
        return $this === self::SuperAdmin || $this === self::Admin;
    }

    public static function isCoreName(string $name): bool
    {
        return self::tryFrom($name)?->isCore() ?? false;
    }
}
