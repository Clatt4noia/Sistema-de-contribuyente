<button
    x-data="{ isDark: document.documentElement.classList.contains('dark') }"
    @click="
        const next = isDark ? 'light' : 'dark';
        fetch('{{ route('theme.update') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ theme: next })
        }).then((response) => {
            if (!response.ok) {
                throw new Error('Theme toggle failed');
            }

            isDark = (next === 'dark');
            const html = document.documentElement;
            html.classList.toggle('dark', isDark);
            html.setAttribute('data-theme', next);
        }).catch(() => {});
    "
    :aria-pressed="isDark"
    class="inline-flex h-6 w-12 items-center rounded-full bg-[color:var(--color-border-strong)] transition"
>
    <span class="inline-block h-5 w-5 rounded-full bg-[color:var(--color-elevated)] transform transition"
          :class="isDark ? 'translate-x-6' : 'translate-x-1'"></span>
    <span class="sr-only">{{ __('Cambiar tema') }}</span>
</button>
