<?php

namespace App\Support;

/**
 * Reads the config/settings.php registry — the single place that knows
 * which keys are valid, which group/is_public each belongs to, and how
 * to cast a key's plain-text `value` column into its real type (Database
 * Design, Section 4.2: "Stored as plain scalar text; cast by the
 * application layer per key").
 */
class Settings
{
    /**
     * Flat key => meta map (['type' => ..., 'is_public' => ..., 'group' => ...]).
     */
    public static function registry(): array
    {
        $flat = [];

        foreach (config('settings', []) as $group => $keys) {
            foreach ($keys as $key => $meta) {
                $flat[$key] = [...$meta, 'group' => $group];
            }
        }

        return $flat;
    }

    public static function isValidKey(string $key): bool
    {
        return array_key_exists($key, self::registry());
    }

    public static function groupFor(string $key): ?string
    {
        return self::registry()[$key]['group'] ?? null;
    }

    public static function isPublic(string $key): bool
    {
        return (bool) (self::registry()[$key]['is_public'] ?? false);
    }

    /**
     * Casts a setting's raw stored value to its declared type for API
     * output. Unknown keys are returned as-is (string), since they can
     * only exist from a stale registry, never from user input. 'json' is
     * for settings that are genuinely a small list of records (e.g. the
     * Homepage Builder's "Why Choose Us" feature list) rather than a
     * scalar — still one column, still no migration needed to add one,
     * per this table's whole reason for existing (Database Design,
     * Section 4.2).
     */
    public static function cast(string $key, ?string $value): string|int|bool|array|null
    {
        if ($value === null) {
            return null;
        }

        return match (self::registry()[$key]['type'] ?? 'string') {
            'int' => (int) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
