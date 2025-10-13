@props(['theme' => null])

@php
    $resolvedTheme = in_array($theme, ['light', 'dark'], true)
        ? $theme
        : \App\Support\Theme::resolve();
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
