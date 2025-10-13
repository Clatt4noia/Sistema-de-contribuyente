<div class="flex flex-wrap gap-3">
 <a
 @class([
 'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
 'bg-slate-200 text-white hover:bg-sky-600 focus-visible:outline-sky-600 ' => $links['pdf'],
 'pointer-events-none bg-slate-200 text-slate-400 ' => ! $links['pdf'],
 ])
 href="{{ $links['pdf'] ?? '#' }}"
 >
 <x-heroicon-o-printer class="h-4 w-4" /> PDF
 </a>

 <a
 @class([
 'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
 'bg-emerald-500 text-white hover:bg-emerald-600 focus-visible:outline-emerald-500 ' => $xmlAvailable,
 'pointer-events-none bg-slate-200 text-slate-400 ' => ! $xmlAvailable,
 ])
 href="{{ $links['xml'] ?? '#' }}"
 >
 <x-heroicon-o-document-text class="h-4 w-4" /> XML
 </a>

 <a
 @class([
 'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
 'bg-indigo-500 text-white hover:bg-indigo-600 focus-visible:outline-indigo-500 ' => $cdrAvailable,
 'pointer-events-none bg-slate-200 text-slate-400 ' => ! $cdrAvailable,
 ])
 href="{{ $links['cdr'] ?? '#' }}"
 >
 <x-heroicon-o-archive-box class="h-4 w-4" /> CDR
 </a>
</div>
