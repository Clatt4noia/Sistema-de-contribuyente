<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-slate-900 ">
 {{ $isEdit ? 'Editar Cliente' : 'Registrar Cliente' }}
 </h1>

 <a
 href="{{ route('clients.index') }}"
 class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 "
 >
 Volver
 </a>
 </div>

 <div class="surface-card p-6">
 <form wire:submit.prevent="save" class="grid grid-cols-1 gap-6 md:grid-cols-2">
 <div class="form-field">
 <label for="business_name" class="form-label">Razón social *</label>
 <input
 type="text"
 id="business_name"
 wire:model.defer="form.business_name"
 @class([
 'form-control',
 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' => $errors->has('form.business_name'),
 ])
 >
 @error('form.business_name')
 <span class="text-sm font-medium text-rose-500">{{ $message }}</span>
 @enderror
 </div>

 <div class="form-field">
 <label for="tax_id" class="form-label">RUC *</label>
 <input
 type="text"
 id="tax_id"
 wire:model.defer="form.tax_id"
 @class([
 'form-control',
 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' => $errors->has('form.tax_id'),
 ])
 >
 @error('form.tax_id')
 <span class="text-sm font-medium text-rose-500">{{ $message }}</span>
 @enderror
 </div>

 <div class="form-field">
 <label for="contact_name" class="form-label">Contacto</label>
 <input
 type="text"
 id="contact_name"
 wire:model.defer="form.contact_name"
 @class([
 'form-control',
 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' => $errors->has('form.contact_name'),
 ])
 >
 @error('form.contact_name')
 <span class="text-sm font-medium text-rose-500">{{ $message }}</span>
 @enderror
 </div>

 <div class="form-field">
 <label for="email" class="form-label">Correo</label>
 <input
 type="email"
 id="email"
 wire:model.defer="form.email"
 @class([
 'form-control',
 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' => $errors->has('form.email'),
 ])
 >
 @error('form.email')
 <span class="text-sm font-medium text-rose-500">{{ $message }}</span>
 @enderror
 </div>

 <div class="form-field">
 <label for="phone" class="form-label">Teléfono</label>
 <input
 type="text"
 id="phone"
 wire:model.defer="form.phone"
 @class([
 'form-control',
 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' => $errors->has('form.phone'),
 ])
 >
 @error('form.phone')
 <span class="text-sm font-medium text-rose-500">{{ $message }}</span>
 @enderror
 </div>

 <div class="form-field">
 <label for="billing_address" class="form-label">Dirección de facturación</label>
 <input
 type="text"
 id="billing_address"
 wire:model.defer="form.billing_address"
 @class([
 'form-control',
 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' => $errors->has('form.billing_address'),
 ])
 >
 @error('form.billing_address')
 <span class="text-sm font-medium text-rose-500">{{ $message }}</span>
 @enderror
 </div>

 <div class="form-field">
 <label for="payment_terms" class="form-label">Condiciones de pago</label>
 <input
 type="text"
 id="payment_terms"
 placeholder="30 días, contado..."
 wire:model.defer="form.payment_terms"
 @class([
 'form-control',
 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' => $errors->has('form.payment_terms'),
 ])
 >
 @error('form.payment_terms')
 <span class="text-sm font-medium text-rose-500">{{ $message }}</span>
 @enderror
 </div>

 <div class="form-field md:col-span-2">
 <label for="notes" class="form-label">Notas</label>
 <textarea
 id="notes"
 rows="4"
 wire:model.defer="form.notes"
 @class([
 'form-control',
 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' => $errors->has('form.notes'),
 ])
 ></textarea>
 @error('form.notes')
 <span class="text-sm font-medium text-rose-500">{{ $message }}</span>
 @enderror
 </div>

 <div class="md:col-span-2 flex justify-end">
 <button
 type="submit"
 class="inline-flex items-center gap-2 rounded-xl bg-indigo-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 "
 >
 {{ $isEdit ? 'Actualizar' : 'Guardar' }}
 </button>
 </div>
 </form>
 </div>
</div>
