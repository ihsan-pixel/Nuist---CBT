<?php

namespace App\Casts;

use App\Enums\UserRole;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class UserRoleCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?UserRole
    {
        if (! is_string($value) || $value === '') {
            return UserRole::Peserta;
        }

        return match ($value) {
            'admin', 'super_admin' => UserRole::SuperAdmin,
            'panitia' => UserRole::Panitia,
            'peserta' => UserRole::Peserta,
            default => throw new InvalidArgumentException("Invalid role value [{$value}] for {$key}."),
        };
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof UserRole) {
            return $value->value;
        }

        if (! is_string($value) || $value === '') {
            return UserRole::Peserta->value;
        }

        return match ($value) {
            'admin' => UserRole::SuperAdmin->value,
            'super_admin', 'panitia', 'peserta' => $value,
            default => throw new InvalidArgumentException("Invalid role value [{$value}] for {$key}."),
        };
    }
}
