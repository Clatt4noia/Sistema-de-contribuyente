<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class SunatDashboardFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date', 'before_or_equal:date_to'],
            'date_to' => ['nullable', 'date'],
            'series' => ['nullable', 'string', 'max:10'],
            'document_type' => ['nullable', 'in:all,invoice,gre'],
            'sunat_status' => ['nullable', 'in:,all,aceptado,rechazado,pendiente,observado'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (($validated['sunat_status'] ?? null) === 'all') {
            $validated['sunat_status'] = '';
        }

        return $validated;
    }
}
