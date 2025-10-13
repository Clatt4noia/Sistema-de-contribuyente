<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<script>
    (function () {
        if (typeof window === 'undefined' || typeof document === 'undefined') {
            return;
        }

        var storageKey = 'app:theme';
        var storageSourceKey = storageKey + ':source';
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

        var readStorageValue = function (key) {
            try {
                return window.localStorage.getItem(key);

            } catch (error) {
                return null;
            }
        };

        var writeStorageValue = function (key, value) {
            try {
                window.localStorage.setItem(key, value);
            } catch (error) {
                // Ignore storage write errors
            }
        };

        var applyTheme = function (theme) {
            var normalized = theme === 'dark' ? 'dark' : 'light';
            root.classList.toggle('dark', normalized === 'dark');
            root.setAttribute('data-theme', normalized);

            return normalized;
        };

        var storedTheme = readStorageValue(storageKey);
        var storedSource = readStorageValue(storageSourceKey);
        var cookieTheme = readCookie(cookieKey);

        var initialTheme = (storedTheme === 'light' || storedTheme === 'dark')
            ? storedTheme
            : (cookieTheme === 'light' || cookieTheme === 'dark')
                ? cookieTheme
                : mediaQuery.matches
                    ? 'dark'
                    : 'light';

        var normalizedInitial = applyTheme(initialTheme);

        var initialSource = storedTheme
            ? (storedSource === 'system' ? 'system' : 'user')
            : (cookieTheme ? 'user' : 'system');

        if (!storedTheme && !cookieTheme) {
            initialSource = 'system';
        }

        if (cookieTheme !== normalizedInitial) {
            writeCookie(normalizedInitial);
        }

        if (storedTheme !== normalizedInitial) {
            writeStorageValue(storageKey, normalizedInitial);
        }

        if (storedSource !== initialSource) {
            writeStorageValue(storageSourceKey, initialSource);
        }


        var controller = {
            value: normalizedInitial,
        };

        var listeners = new Set();

        var notify = function () {
            listeners.forEach(function (callback) {
                try {
                    callback(controller.value);
                } catch (error) {
                    // Ignore subscriber errors
                }
            });
        };

        var subscribe = function (callback) {
            if (typeof callback !== 'function') {
                return function () {};
            }

            callback(controller.value);
            listeners.add(callback);

            return function () {
                listeners.delete(callback);
            };
        };

        var preferenceSource = initialSource === 'system' ? 'system' : 'user';

        var setTheme = function (theme, options) {
            var normalized = applyTheme(theme);
            var changed = controller.value !== normalized;

            controller.value = normalized;

            if (options && options.persist === false) {
                if (options && options.source) {
                    preferenceSource = options.source === 'user' ? 'user' : 'system';
                }

                if (!options || options.cookie !== false) {
                    writeCookie(normalized);
                }

                if (changed || (options && options.notify)) {
                    notify();
                }

                return normalized;
            }

            var nextSource = options && options.source === 'system' ? 'system' : 'user';

            if (preferenceSource !== nextSource) {
                preferenceSource = nextSource;
            }

            writeStorageValue(storageKey, normalized);
            writeStorageValue(storageSourceKey, preferenceSource);
            writeCookie(normalized);

            if (changed || (options && options.notify)) {
                notify();
            }

            return normalized;
        };

        var toggleTheme = function () {
            var next = controller.value === 'dark' ? 'light' : 'dark';
            return setTheme(next);
        };

        controller.get = function () {
            return controller.value;
        };

        controller.set = setTheme;
        controller.toggle = toggleTheme;
        controller.subscribe = subscribe;

        window.__appTheme = controller;

        mediaQuery.addEventListener('change', function (event) {
            if (preferenceSource === 'user') {
                return;
            }

            setTheme(event.matches ? 'dark' : 'light', { source: 'system' });
        });

        window.addEventListener('storage', function (event) {
            if (event.key === storageKey) {
                if (event.newValue === 'light' || event.newValue === 'dark') {
                    var source = readStorageValue(storageSourceKey) === 'system' ? 'system' : 'user';
                    setTheme(event.newValue, { persist: false, source: source, notify: true });
                }

                return;
            }

            if (event.key === storageSourceKey) {
                preferenceSource = event.newValue === 'system' ? 'system' : 'user';
            }
        });

        document.addEventListener('alpine:init', function () {
            if (!window.Alpine) {
                return;
            }

            var store = {
                current: controller.value,
                get isDark() {
                    return this.current === 'dark';
                },

                set: function (theme) {
                    controller.set(theme);
                },
                toggle: function () {
                    controller.toggle();
                },
                subscribe: function (callback) {
                    return controller.subscribe(callback);
                },

            };

            Alpine.store('theme', store);

            controller.subscribe(function (theme) {
                store.current = theme;
            });

            Alpine.data('appThemeToggle', function () {
                return {
                    isDark: controller.value === 'dark',
                    unsubscribe: null,
                    init: function () {
                        var self = this;
                        this.unsubscribe = controller.subscribe(function (theme) {
                            self.isDark = theme === 'dark';
                        });
                    },
                    toggle: function () {
                        controller.toggle();
                    },
                    destroy: function () {
                        if (this.unsubscribe) {
                            this.unsubscribe();
                            this.unsubscribe = null;
                        }
                    },
                };
            });
        });

        window.appThemeToggle = function () {
            return {
                isDark: controller.value === 'dark',
                unsubscribe: null,
                init: function () {
                    var self = this;
                    this.unsubscribe = controller.subscribe(function (theme) {
                        self.isDark = theme === 'dark';
                    });
                },
                toggle: function () {
                    controller.toggle();
                },
                destroy: function () {
                    if (this.unsubscribe) {
                        this.unsubscribe();
                        this.unsubscribe = null;
                    }
                },
            };
        };

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
