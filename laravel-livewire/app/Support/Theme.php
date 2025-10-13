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

        return in_array($value, ['light', 'dark'], true) ? $value : 'light';
    }

    /**
     * Determine if the resolved theme is dark.
     */
    public static function isDark(?Request $request = null): bool
    {
        return self::resolve($request) === 'dark';
    }
}
