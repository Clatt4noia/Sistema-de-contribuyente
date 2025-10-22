<!DOCTYPE html>
<html {{ $attributes->merge(['lang' => str_replace('_', '-', app()->getLocale())]) }}>
    {{ $slot }}
</html>
