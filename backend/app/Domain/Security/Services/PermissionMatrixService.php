<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

use App\Models\RolePermissionMatrix;

final class PermissionMatrixService
{
    /**
     * @return array<int, string>
     */
    public function roles(): array
    {
        return ['admin', 'editor', 'ops', 'finance'];
    }

    /**
     * @return array<int, string>
     */
    public function permissionKeys(): array
    {
        return [
            'permissions.manage',
            'dashboard.view',
            'alerts.view',
            'catalog.manage',
            'nominal.manage',
            'payment.manage',
            'pricing.manage',
            'promo.manage',
            'orders.manage',
            'customers.manage',
            'support.manage',
            'reviews.manage',
            'audit.view',
            'security.view',
            'cms.manage',
            'seo.manage',
        ];
    }

    public function resolvePermissionKey(?string $routeName): ?string
    {
        $name = (string) ($routeName ?? '');

        if ($name === '' || !str_starts_with($name, 'admin.')) {
            return null;
        }

        if (str_starts_with($name, 'admin.permissions.')) {
            return 'permissions.manage';
        }

        if (str_starts_with($name, 'admin.dashboard.alerts')) {
            return 'alerts.view';
        }

        if (str_starts_with($name, 'admin.dashboard')) {
            return 'dashboard.view';
        }

        if (str_starts_with($name, 'admin.catalog.')) {
            return 'catalog.manage';
        }

        if (str_starts_with($name, 'admin.nominal.')) {
            return 'nominal.manage';
        }

        if (str_starts_with($name, 'admin.payment.')) {
            return 'payment.manage';
        }

        if (str_starts_with($name, 'admin.pricing.')) {
            return 'pricing.manage';
        }

        if (str_starts_with($name, 'admin.promo.')) {
            return 'promo.manage';
        }

        if (str_starts_with($name, 'admin.orders.')) {
            return 'orders.manage';
        }

        if (str_starts_with($name, 'admin.customers.')) {
            return 'customers.manage';
        }

        if (str_starts_with($name, 'admin.support.')) {
            return 'support.manage';
        }

        if (str_starts_with($name, 'admin.reviews.')) {
            return 'reviews.manage';
        }

        if (str_starts_with($name, 'admin.audit-logs.')) {
            return 'audit.view';
        }

        if (str_starts_with($name, 'admin.security-events.')) {
            return 'security.view';
        }

        if (str_starts_with($name, 'admin.cms.')) {
            return 'cms.manage';
        }

        if (str_starts_with($name, 'admin.seo.')) {
            return 'seo.manage';
        }

        return null;
    }

    public function isAllowed(string $role, ?string $permissionKey): bool
    {
        $normalizedRole = strtolower(trim($role));
        $key = trim((string) $permissionKey);

        if ($key === '') {
            return true;
        }

        $entry = RolePermissionMatrix::query()
            ->where('role', $normalizedRole)
            ->where('permission_key', $key)
            ->first();

        if ($entry === null) {
            return $normalizedRole === 'admin';
        }

        return (bool) $entry->is_allowed;
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public function matrixSnapshot(): array
    {
        $roles = $this->roles();
        $keys = $this->permissionKeys();

        $existing = RolePermissionMatrix::query()
            ->whereIn('role', $roles)
            ->whereIn('permission_key', $keys)
            ->get(['role', 'permission_key', 'is_allowed'])
            ->groupBy('role');

        $matrix = [];
        foreach ($roles as $role) {
            $matrix[$role] = [];
            foreach ($keys as $key) {
                $row = $existing[$role]?->firstWhere('permission_key', $key);
                $matrix[$role][$key] = $row !== null ? (bool) $row->is_allowed : ($role === 'admin');
            }
        }

        return $matrix;
    }
}
