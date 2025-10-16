<div class="space-y-6">
 <section class="grid gap-6 lg:grid-cols-[2fr_1fr]">
 <article class="surface-card">
 <header class="flex items-center justify-between border-b border-slate-200 px-6 py-5 ">
 <div>
 <h1 class="text-2xl font-semibold text-slate-900 ">{{ __('Bienvenido a tu portal logístico') }}</h1>
 <p class="mt-1 text-sm text-slate-600 ">{{ __('Consulta el estado de tus servicios en tiempo real y mantén tus pagos al día.') }}</p>
 </div>
 </header>
 <div class="grid gap-4 p-6 sm:grid-cols-3">
 <x-dashboard.stat :label="__('Servicios registrados')" :value="$metrics['orders']" icon="navigation" />
 <x-dashboard.stat :label="__('Facturas emitidas')" :value="$metrics['invoices']" icon="receipt" />
 <x-dashboard.stat :label="__('Facturas pendientes')" :value="$metrics['openInvoices']" icon="alert-circle" />
 </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Canales de atención') }}</h2>
 </header>
 <div class="space-y-3 p-6 text-sm text-slate-600 ">
 <p>{{ __('Si necesitas soporte inmediato puedes escribir a nuestro equipo de operaciones o llamar al ejecutivo asignado.') }}</p>
 <p class="font-medium text-slate-900 ">{{ __('Correo de contacto:') }} <span class="font-semibold text-accent-soft ">{{ $contactEmail }}</span></p>
 </div>
 </article>
 </section>

 <section class="grid gap-6 lg:grid-cols-2">
 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Servicios recientes') }}</h2>

 </header>
    <div class="overflow-x-auto">
      <table class="table table-md">
        <thead>
          <tr class="table-row">
            <th class="table-header">{{ __('Referencia') }}</th>
            <th class="table-header">{{ __('Origen') }}</th>
            <th class="table-header">{{ __('Destino') }}</th>
            <th class="table-header">{{ __('Estado') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($orders as $order)
            <tr class="table-row table-row-hover">
              <td class="table-cell text-sm font-medium text-slate-900 ">{{ $order->reference ?? '—' }}</td>
              <td class="table-cell text-sm text-slate-600 ">{{ $order->origin ?? '—' }}</td>
              <td class="table-cell text-sm text-slate-600 ">{{ $order->destination ?? '—' }}</td>
              <td class="table-cell">
                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 ">{{ $order->status ? __($order->status) : __('pendiente') }}</span>
              </td>
            </tr>
          @empty
            <tr class="table-row">
              <td colspan="4" class="table-empty">{{ __('No tienes servicios activos en este momento.') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
 </article>

 <article class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Facturas recientes') }}</h2>
 </header>
    <div class="overflow-x-auto">
      <table class="table table-md">
        <thead>
          <tr class="table-row">
            <th class="table-header">{{ __('Número') }}</th>
            <th class="table-header">{{ __('Monto') }}</th>
            <th class="table-header">{{ __('Estado') }}</th>
            <th class="table-header">{{ __('Emisión') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($invoices as $invoice)
            <tr class="table-row table-row-hover">
              <td class="table-cell text-sm font-medium text-slate-900 ">{{ $invoice->invoice_number ?? '—' }}</td>
              <td class="table-cell text-sm text-slate-600 ">{{ \App\Support\Formatters\MoneyFormatter::pen($invoice->total) }}</td>
              <td class="table-cell">
                <span class="rounded-full bg-success-soft px-3 py-1 text-xs font-semibold text-success-strong ">{{ $invoice->status ? __($invoice->status) : __('pendiente') }}</span>

              </td>
              <td class="table-cell text-sm text-slate-600 ">{{ optional($invoice->issue_date)?->format('d/m/Y') ?? '—' }}</td>
            </tr>
          @empty
            <tr class="table-row">
              <td colspan="4" class="table-empty">{{ __('No hay facturas registradas aún.') }}</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
 </article>
 </section>


 <section class="surface-card">
 <header class="border-b border-slate-200 px-6 py-5 ">
 <h2 class="text-lg font-semibold text-slate-900 ">{{ __('Transparencia y seguridad') }}</h2>
 </header>
 <div class="space-y-3 p-6 text-sm text-slate-600 ">
 <p>{{ __('Separar los paneles evita que otros clientes vean información sensible.') }}</p>
 <p>{{ __('Tu cuenta sólo puede visualizar servicios y facturas que te pertenecen.') }}</p>
 </div>
 </section>
</div>
