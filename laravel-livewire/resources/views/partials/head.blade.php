<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<script>
    (function () {
        if (typeof window === 'undefined' || typeof document === 'undefined') {
            return;
        }

        var storageKey = 'app:theme';
        var cookieKey = 'app_theme';
        var cookieTtl = 60 * 60 * 24 * 365; // 1 year
        var root = document.documentElement;
        var mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        var readCookie = function (key) {
            try {
                return document.cookie
                    .split(';')
                    .map(function (entry) {
                        return entry.trim().split('=');
                    })
                    .find(function (pair) {
                        return pair[0] === key;
                    })?.[1] || null;
            } catch (error) {
                return null;
            }
        };

        var writeCookie = function (theme) {
            try {
                document.cookie = cookieKey + '=' + theme + '; path=/; max-age=' + cookieTtl + '; SameSite=Lax';
            } catch (error) {
                // Ignore cookie write errors
            }
        };

        var readStorage = function () {
            try {
                return window.localStorage.getItem(storageKey);
            } catch (error) {
                return null;
            }
        };

        var writeStorage = function (theme) {
            try {
                window.localStorage.setItem(storageKey, theme);
            } catch (error) {
                // Ignore storage write errors
            }
        };

        var updateToggleState = function (isDark) {
            try {
                document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
                    button.setAttribute('aria-pressed', String(isDark));
                });
            } catch (error) {
                // Ignore DOM access errors
            }
        };

        var applyTheme = function (theme) {
            var normalized = theme === 'dark' ? 'dark' : 'light';
            var isDark = normalized === 'dark';

            root.classList.toggle('dark', isDark);
            root.setAttribute('data-theme', normalized);
            updateToggleState(isDark);

            return normalized;
        };

        var persistTheme = function (theme) {
            writeStorage(theme);
            writeCookie(theme);
        };

        var syncTheme = function (theme, options) {
            var normalized = applyTheme(theme);

            if (!options || options.persist !== false) {
                persistTheme(normalized);
            } else {
                writeCookie(normalized);
            }
        };

        var resolveTheme = function () {
            var storedTheme = readStorage();
            if (storedTheme === 'light' || storedTheme === 'dark') {
                return storedTheme;
            }

            var cookieTheme = readCookie(cookieKey);
            if (cookieTheme === 'light' || cookieTheme === 'dark') {
                return cookieTheme;
            }

            return mediaQuery.matches ? 'dark' : 'light';
        };

        syncTheme(resolveTheme());

        document.addEventListener('DOMContentLoaded', function () {
            updateToggleState(root.classList.contains('dark'));
        });

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-theme-toggle]');
            if (!trigger) {
                return;
            }

            event.preventDefault();

            var nextTheme = root.classList.contains('dark') ? 'light' : 'dark';
            syncTheme(nextTheme);
        });

        mediaQuery.addEventListener('change', function (event) {
            var stored = readStorage();
            if (stored !== 'light' && stored !== 'dark') {
                syncTheme(event.matches ? 'dark' : 'light');
            }
        });

        window.addEventListener('storage', function (event) {
            if (event.key === storageKey && event.newValue) {
                syncTheme(event.newValue, { persist: false });
            }
        });
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
