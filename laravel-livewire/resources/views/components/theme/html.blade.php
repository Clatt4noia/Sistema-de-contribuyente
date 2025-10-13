@props(['theme' => null])

@php
    $cookieTheme = request()->cookie('app_theme');

    if (in_array($theme, ['light', 'dark'], true)) {
        $resolvedTheme = $theme;
    } elseif (in_array($cookieTheme, ['light', 'dark'], true)) {
        $resolvedTheme = $cookieTheme;
    } else {
        $resolvedTheme = 'light';
    }
@endphp

<!DOCTYPE html>
<html {{ $attributes
    ->merge([
        'lang' => str_replace('_', '-', app()->getLocale()),
        'data-theme' => $resolvedTheme,
    ])
    ->class([
        $resolvedTheme === 'dark' ? 'dark' : null,
    ])
}}>
    {{ $slot }}
</html>
