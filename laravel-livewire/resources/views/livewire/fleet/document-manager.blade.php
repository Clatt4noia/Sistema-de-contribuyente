<div class="surface-card space-y-6 p-6 shadow-lg">
 <div class="flex flex-wrap items-center justify-between gap-3">
 <div>
 <h2 class="text-lg font-semibold text-slate-900 ">Expediente digital</h2>
 <p class="text-sm text-slate-500 ">Centraliza pólizas, SOAT, licencias y certificados.</p>
 </div>
 <div class="text-sm text-slate-500 ">
 {{ __('Documentos registrados: :total', ['total' => count($documents)]) }}
 </div>
 </div>

 <form wire:submit.prevent="save" class="grid gap-4 md:grid-cols-2">
 <div class="form-field">
 <label class="form-label" for="document_type">Tipo de documento</label>
 <select id="document_type" wire:model="form.document_type" class="form-control">
 @foreach($typeOptions as $value => $label)
 <option value="{{ $value }}">{{ $label }}</option>
 @endforeach
 </select>
 @error('form.document_type') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label class="form-label" for="document_title">Nombre</label>
 <input type="text" id="document_title" wire:model.defer="form.title" class="form-control" placeholder="Ej. SOAT 2025">
 @error('form.title') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label class="form-label" for="issued_at">Emitido</label>
 <input type="date" id="issued_at" wire:model.defer="form.issued_at" class="form-control">
 @error('form.issued_at') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field">
 <label class="form-label" for="expires_at">Vence</label>
 <input type="date" id="expires_at" wire:model.defer="form.expires_at" class="form-control">
 @error('form.expires_at') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field md:col-span-2">
 <label class="form-label" for="document_notes">Notas</label>
 <textarea id="document_notes" wire:model.defer="form.notes" rows="3" class="form-control" placeholder="Observaciones opcionales"></textarea>
 @error('form.notes') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="form-field md:col-span-2">
 <label class="form-label" for="document_file">Archivo</label>
 <input type="file" id="document_file" wire:model="file" class="form-control" accept="application/pdf,image/*">
 <p class="mt-1 text-xs text-slate-500 ">Formatos permitidos: PDF, JPG, PNG (máx. 10 MB).</p>
 @error('file') <span class="form-error">{{ $message }}</span> @enderror
 </div>

 <div class="md:col-span-2 flex items-center justify-end gap-3">
 <div wire:loading wire:target="file" class="text-sm text-slate-500 ">
 {{ __('Subiendo archivo...') }}
 </div>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-cloud-upload-alt"></i>
        {{ __('Adjuntar') }}
    </button>
 </div>
 </form>

  <div class="overflow-x-auto">
    <table class="table table-md">
      <thead>
        <tr class="table-row">
          <th class="table-header">Tipo</th>
          <th class="table-header">Nombre</th>
          <th class="table-header">Emitido</th>
          <th class="table-header">Vencimiento</th>
          <th class="table-header">Estado</th>
          <th class="table-header text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @php
          $statusClasses = [
            'valid' => 'bg-success-soft text-success-strong ',
            'warning' => 'bg-warning-soft text-warning ',
            'expired' => 'bg-danger-soft text-danger-strong ',

          ];
        @endphp
        @forelse($documents as $document)
        <tr class="table-row table-row-hover">
          <td class="table-cell text-sm text-slate-600 ">{{ $document['type_label'] }}</td>
          <td class="table-cell text-sm font-medium text-slate-900 ">{{ $document['title'] }}</td>
          <td class="table-cell text-sm text-slate-600 ">{{ $document['issued_at'] ?? '—' }}</td>
          <td class="table-cell text-sm text-slate-600 ">{{ $document['expires_at'] ?? '—' }}</td>
          <td class="table-cell">
            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses[$document['status']] ?? $statusClasses['valid'] }}">
              {{ $document['status_label'] }}
            </span>
          </td>
          <td class="table-cell text-right text-sm">
            <div class="flex items-center justify-end gap-2">
              @if($document['file_url'])
                <a href="{{ $document['file_url'] }}" target="_blank" class="btn btn-secondary btn-sm">
                  <i class="fas fa-file-download"></i>
                  {{ __('Descargar') }}
                </a>
              @endif
              <button type="button" wire:click="deleteDocument({{ $document['id'] }})" class="btn btn-danger btn-sm">
                <i class="fas fa-trash"></i>
                {{ __('Eliminar') }}
              </button>
            </div>
          </td>
        </tr>
      @empty
        <tr class="table-row">
          <td colspan="6" class="table-empty">{{ __('Aún no hay documentos registrados.') }}</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
