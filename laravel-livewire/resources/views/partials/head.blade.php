<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<script>
    (function () {
        if (typeof window === 'undefined' || typeof document === 'undefined') {
            return;
        }

        try {
            var storageKey = 'app:theme';
            var root = document.documentElement;
            var storedTheme = window.localStorage.getItem(storageKey);
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var theme = storedTheme || (prefersDark ? 'dark' : 'light');

            if (theme === 'dark') {
                root.classList.add('dark');
            } else {
                root.classList.remove('dark');
            }

            root.setAttribute('data-theme', theme);
        } catch (error) {
            // Ignore storage access errors (e.g. private browsing)
        }
    })();
</script>

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
@livewireStyles
