<div class="flex flex-wrap gap-3">
    <a
        @class([
            'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
            'bg-slate-900 text-white hover:bg-slate-800 focus-visible:outline-slate-900 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200' => $links['pdf'],
            'pointer-events-none bg-slate-200 text-slate-400 dark:bg-slate-800 dark:text-slate-500' => ! $links['pdf'],
        ])
        href="{{ $links['pdf'] ?? '#' }}"
    >
        <x-heroicon-o-printer class="h-4 w-4" /> PDF
    </a>

    <a
        @class([
            'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
            'bg-emerald-500 text-white hover:bg-emerald-600 focus-visible:outline-emerald-500 dark:bg-emerald-400 dark:text-emerald-950 dark:hover:bg-emerald-300' => $xmlAvailable,
            'pointer-events-none bg-slate-200 text-slate-400 dark:bg-slate-800 dark:text-slate-500' => ! $xmlAvailable,
        ])
        href="{{ $links['xml'] ?? '#' }}"
    >
        <x-heroicon-o-document-text class="h-4 w-4" /> XML
    </a>

    <a
        @class([
            'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
            'bg-indigo-500 text-white hover:bg-indigo-600 focus-visible:outline-indigo-500 dark:bg-indigo-400 dark:text-indigo-950 dark:hover:bg-indigo-300' => $cdrAvailable,
            'pointer-events-none bg-slate-200 text-slate-400 dark:bg-slate-800 dark:text-slate-500' => ! $cdrAvailable,
        ])
        href="{{ $links['cdr'] ?? '#' }}"
    >
        <x-heroicon-o-archive-box class="h-4 w-4" /> CDR
    </a>
</div>
