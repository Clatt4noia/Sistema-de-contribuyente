<div class="flex flex-wrap gap-3">
 <a
    @class([
        'btn btn-secondary' => $links['pdf'],
        'btn btn-secondary opacity-60 pointer-events-none' => ! $links['pdf'],
    ])
 href="{{ $links['pdf'] ?? '#' }}"
 >
 <x-heroicon-o-printer class="h-4 w-4" /> PDF
 </a>

 <a
    @class([
        'btn btn-primary' => $xmlAvailable,
        'btn btn-primary opacity-60 pointer-events-none' => ! $xmlAvailable,
    ])
 href="{{ $links['xml'] ?? '#' }}"
 >
 <x-heroicon-o-document-text class="h-4 w-4" /> XML
 </a>

 <a
    @class([
        'btn btn-secondary' => $cdrAvailable,
        'btn btn-secondary opacity-60 pointer-events-none' => ! $cdrAvailable,
    ])
 href="{{ $links['cdr'] ?? '#' }}"
 >
 <x-heroicon-o-archive-box class="h-4 w-4" /> CDR
 </a>
</div>
