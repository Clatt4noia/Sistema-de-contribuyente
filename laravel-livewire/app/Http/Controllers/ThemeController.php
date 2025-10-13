<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ThemeController extends Controller
{
    public function update(Request $request)
    {
        $theme = $request->string('theme')->toString();

        if (! in_array($theme, ['light', 'dark'], true)) {
            return response()->json(['message' => 'Invalid theme'], 422);
        }

        if (Auth::check() && Schema::hasColumn('users', 'theme')) {
            $user = Auth::user();
            $user->theme = $theme;
            $user->save();
        }

        return response()->noContent()->withCookie(
            cookie('app_theme', $theme, 60 * 24 * 365, '/', null, false, false, false, 'lax')
        );
    }
}
