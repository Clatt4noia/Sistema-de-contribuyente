<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\InvoiceAudit;
use Illuminate\Support\Facades\Auth;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        $this->storeAudit($invoice, 'creado', null, $invoice->getAttributes());
    }

    public function updated(Invoice $invoice): void
    {
        $old = array_intersect_key($invoice->getOriginal(), $invoice->getChanges());
        $new = $invoice->getChanges();

        if (! empty($new)) {
            $this->storeAudit($invoice, 'actualizado', $old, $new);
        }
    }

    public function deleted(Invoice $invoice): void
    {
        $this->storeAudit($invoice, 'eliminado', $invoice->getOriginal(), null);
    }

    protected function storeAudit(Invoice $invoice, string $event, ?array $old, ?array $new): void
    {
        InvoiceAudit::create([
            'invoice_id' => $invoice->getKey(),
            'event' => $event,
            'performed_by' => optional(Auth::user())->getAuthIdentifier(),
            'old_values' => $old,
            'new_values' => $new,
            'created_at' => now(),
        ]);
    }
}
