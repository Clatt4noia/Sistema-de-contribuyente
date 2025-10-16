<div class="mx-auto max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
 <div class="flex flex-wrap items-center justify-between gap-4">
 <h1 class="text-2xl font-semibold text-slate-900 ">
 {{ $isEdit ? 'Editar Cliente' : 'Registrar Cliente' }}
 </h1>

    <a
        href="{{ route('clients.index') }}"
        class="btn btn-secondary"
    >
        Volver
    </a>
 </div>

 <div class="surface-card p-6">
 <form wire:submit.prevent="save" class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="form-field">
            <label for="business_name" class="form-label">
                <span class="required">Razón social</span>
            </label>
            <input
                type="text"
                id="business_name"
                wire:model.defer="form.business_name"
                @class([
                    'form-control form-md',
                    'is-invalid' => $errors->has('form.business_name'),
                ])
                @error('form.business_name') aria-invalid="true" aria-describedby="business_name-error" @enderror
            >
            @error('form.business_name')
                <p id="business_name-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label for="tax_id" class="form-label">
                <span class="required">RUC</span>
            </label>
            <input
                type="text"
                id="tax_id"
                wire:model.defer="form.tax_id"
                @class([
                    'form-control form-md',
                    'is-invalid' => $errors->has('form.tax_id'),
                ])
                @error('form.tax_id') aria-invalid="true" aria-describedby="tax_id-error" @enderror
            >
            @error('form.tax_id')
                <p id="tax_id-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label for="contact_name" class="form-label">Contacto</label>
            <input
                type="text"
                id="contact_name"
                wire:model.defer="form.contact_name"
                @class([
                    'form-control form-md',
                    'is-invalid' => $errors->has('form.contact_name'),
                ])
                @error('form.contact_name') aria-invalid="true" aria-describedby="contact_name-error" @enderror
            >
            @error('form.contact_name')
                <p id="contact_name-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label for="email" class="form-label">Correo</label>
 <input
                type="email"
                id="email"
                wire:model.defer="form.email"
                @class([
                    'form-control form-md',
                    'is-invalid' => $errors->has('form.email'),
                ])
                @error('form.email') aria-invalid="true" aria-describedby="email-error" @enderror
            >
            @error('form.email')
                <p id="email-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label for="phone" class="form-label">Teléfono</label>
            <input
                type="text"
                id="phone"
                wire:model.defer="form.phone"
                @class([
                    'form-control form-md',
                    'is-invalid' => $errors->has('form.phone'),
                ])
                @error('form.phone') aria-invalid="true" aria-describedby="phone-error" @enderror
            >
            @error('form.phone')
                <p id="phone-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field">
            <label for="billing_address" class="form-label">Dirección de facturación</label>
 <input
                type="text"
                id="billing_address"
                wire:model.defer="form.billing_address"
                @class([
                    'form-control form-md',
                    'is-invalid' => $errors->has('form.billing_address'),
                ])
                @error('form.billing_address') aria-invalid="true" aria-describedby="billing_address-error" @enderror
            >
            @error('form.billing_address')
                <p id="billing_address-error" class="form-error">{{ $message }}</p>
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
                    'form-control form-md',
                    'is-invalid' => $errors->has('form.payment_terms'),
                ])
                @error('form.payment_terms') aria-invalid="true" aria-describedby="payment_terms-error" @enderror
            >
            @error('form.payment_terms')
                <p id="payment_terms-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-field md:col-span-2">
            <label for="notes" class="form-label">Notas</label>
 <textarea
 id="notes"
                rows="4"
                wire:model.defer="form.notes"
                @class([
                    'form-control form-md',
                    'is-invalid' => $errors->has('form.notes'),
                ])
                @error('form.notes') aria-invalid="true" aria-describedby="notes-error" @enderror
            ></textarea>
            @error('form.notes')
                <p id="notes-error" class="form-error">{{ $message }}</p>
            @enderror
        </div>

 <div class="md:col-span-2 flex justify-end">
    <button
        type="submit"
        class="btn btn-primary"
    >
        {{ $isEdit ? 'Actualizar' : 'Guardar' }}
    </button>
 </div>
 </form>
 </div>
</div>
