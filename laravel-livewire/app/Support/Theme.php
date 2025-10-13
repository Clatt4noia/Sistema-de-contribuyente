<?php

namespace App\Support;

use Illuminate\Http\Request;

class Theme
{
    /**
     * Resolve the preferred theme (light or dark) from the incoming request.
     */
    public static function resolve(?Request $request = null): string
    {
        $request ??= request();

        if (! $request instanceof Request) {
            return 'light';
        }

        $value = $request->cookie('app_theme');

        if (in_array($value, ['light', 'dark'], true)) {
            return $value;
        }

        $headerPreference = $request->header('Sec-CH-Prefers-Color-Scheme')
            ?? $request->header('sec-ch-prefers-color-scheme')
            ?? $request->header('Prefer-Color-Scheme');

        if (is_array($headerPreference)) {
            $headerPreference = reset($headerPreference) ?: null;
        }

        if (is_string($headerPreference)) {
            $headerPreference = strtolower(trim($headerPreference));

            if (in_array($headerPreference, ['dark', 'light'], true)) {
                return $headerPreference;
            }

            if (str_contains($headerPreference, 'dark')) {
                return 'dark';
            }

            if (str_contains($headerPreference, 'light')) {
                return 'light';
            }
        }

        return 'light';
    }

    /**
     * Determine if the resolved theme is dark.
     */
    public static function isDark(?Request $request = null): bool
    {
        return self::resolve($request) === 'dark';
    }
}
