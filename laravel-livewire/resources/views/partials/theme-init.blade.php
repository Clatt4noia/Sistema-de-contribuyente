{{-- BEGIN THEME INIT --}}
<script>
(function () {
    try {
        var d = document.documentElement;
        var LS_KEY = 'app:theme';
        var CK_KEY = 'app_theme';
        var listeners = [];
        var hasUserChoice = false;

        function getCookie(name) {
            try {
                var parts = document.cookie.split('; ');
                for (var i = 0; i < parts.length; i++) {
                    var segment = parts[i];
                    if (!segment) {
                        continue;
                    }

                    var tuple = segment.split('=');
                    if (tuple[0] === name) {
                        return decodeURIComponent(tuple.slice(1).join('='));
                    }
                }
            } catch (error) {}

            return null;
        }

        function setCookie(name, val) {
            try {
                document.cookie = name + '=' + encodeURIComponent(val) + '; Max-Age=31536000; Path=/; SameSite=Lax';
            } catch (error) {}
        }

        function sysPrefersDark() {
            try {
                return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            } catch (error) {
                return false;
            }
        }

        function normalize(value) {
            return value === 'dark' ? 'dark' : value === 'light' ? 'light' : null;
        }

        function apply(mode) {
            var normalized = normalize(mode) || 'light';
            d.classList.toggle('dark', normalized === 'dark');
            d.setAttribute('data-theme', normalized);
            return normalized;
        }

        function readLocalStorage(key) {
            try {
                return window.localStorage.getItem(key);
            } catch (error) {
                return null;
            }
        }

        function writeLocalStorage(key, value) {
            try {
                window.localStorage.setItem(key, value);
            } catch (error) {}
        }

        var stored = normalize(readLocalStorage(LS_KEY));
        var cookie = normalize(getCookie(CK_KEY));
        hasUserChoice = !!(stored || cookie);
        var preferred = stored || cookie || (sysPrefersDark() ? 'dark' : 'light');

        preferred = apply(preferred);
        writeLocalStorage(LS_KEY, preferred);
        setCookie(CK_KEY, preferred);

        function notify(mode) {
            for (var i = 0; i < listeners.length; i++) {
                var fn = listeners[i];
                try {
                    fn(mode);
                } catch (error) {}
            }
        }

        window.__appTheme = {
            get: function () {
                return d.classList.contains('dark') ? 'dark' : 'light';
            },
            set: function (mode) {
                var normalized = normalize(mode) || 'light';
                hasUserChoice = true;
                apply(normalized);
                writeLocalStorage(LS_KEY, normalized);
                setCookie(CK_KEY, normalized);
                notify(normalized);
            },
            toggle: function () {
                this.set(this.get() === 'dark' ? 'light' : 'dark');
            },
            subscribe: function (fn) {
                if (typeof fn !== 'function') {
                    return function () {};
                }

                listeners.push(fn);
                try {
                    fn(this.get());
                } catch (error) {}

                return function () {
                    listeners = listeners.filter(function (callback) {
                        return callback !== fn;
                    });
                };
            }
        };

        if (window.matchMedia) {
            var media = window.matchMedia('(prefers-color-scheme: dark)');
            var onSystemChange = function (event) {
                if (hasUserChoice) {
                    return;
                }

                var nextMode = event.matches ? 'dark' : 'light';
                apply(nextMode);
                writeLocalStorage(LS_KEY, nextMode);
                setCookie(CK_KEY, nextMode);
                notify(nextMode);
            };

            if (typeof media.addEventListener === 'function') {
                media.addEventListener('change', onSystemChange);
            } else if (typeof media.addListener === 'function') {
                media.addListener(onSystemChange);
            }
        }

        window.addEventListener('storage', function (event) {
            if (event.key !== LS_KEY) {
                return;
            }

            var next = normalize(event.newValue);
            if (!next) {
                return;
            }

            hasUserChoice = true;
            apply(next);
            setCookie(CK_KEY, next);
            notify(next);
        });
    } catch (error) {}
})();
</script>

<script>
document.addEventListener('alpine:init', function () {
    if (!window.Alpine || !window.__appTheme) {
        return;
    }

    var store = {
        isDark: window.__appTheme.get() === 'dark',
        set: function (mode) {
            window.__appTheme.set(mode);
            this.isDark = window.__appTheme.get() === 'dark';
        },
        toggle: function () {
            window.__appTheme.toggle();
            this.isDark = window.__appTheme.get() === 'dark';
        },
        subscribe: function (fn) {
            return window.__appTheme.subscribe(fn);
        }
    };

    window.__appTheme.subscribe(function (mode) {
        store.isDark = mode === 'dark';
    });

    Alpine.store('theme', store);

    window.appThemeToggle = function () {
        var unsubscribe = function () {};
        return {
            isDark: Alpine.store('theme').isDark,
            init: function () {
                var self = this;
                var themeStore = Alpine.store('theme');
                unsubscribe = themeStore.subscribe(function (mode) {
                    self.isDark = mode === 'dark';
                });
                this.isDark = themeStore.isDark;
            },
            toggle: function () {
                Alpine.store('theme').toggle();
            },
            destroy: function () {
                try {
                    unsubscribe();
                } catch (error) {}
                unsubscribe = function () {};
            }
        };
    };
});
</script>
{{-- END THEME INIT --}}
