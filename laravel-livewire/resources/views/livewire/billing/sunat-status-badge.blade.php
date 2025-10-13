@php
 $labels = [
 'aceptado' => 'Aceptado',
 'rechazado' => 'Rechazado',
 'observado' => 'Observado',
 'pendiente' => 'Pendiente',
 ];
@endphp

<span class="inline-flex items-center gap-2 rounded-full bg-{{ $variant }}-100 px-3 py-1 text-xs font-semibold text-{{ $variant }}-700 $variant }}-500/20 $variant }}-200">
 <span class="inline-block h-2 w-2 rounded-full bg-{{ $variant }}-500"></span>
 {{ $labels[$status] ?? ucfirst($status) }}
 @if($message)
 <span class="hidden text-[10px] text-slate-500 sm:inline">{{ $message }}</span>
 @endif
</span>
