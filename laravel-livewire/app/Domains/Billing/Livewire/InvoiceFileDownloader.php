<?php

namespace App\Domains\Billing\Livewire;

use App\Models\Invoice;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class InvoiceFileDownloader extends Component
{
    use AuthorizesRequests;

    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        $this->authorize('view', $invoice);
        $this->invoice = $invoice;
    }

    public function render()
    {
        $storageDisk = config('greenter.storage.disk_xml_cdr');

        $xmlAvailable = $this->invoice->xml_path && Storage::disk($storageDisk)->exists($this->invoice->xml_path);
        $cdrAvailable = $this->invoice->cdr_path && Storage::disk($storageDisk)->exists($this->invoice->cdr_path);

        $links = [
            'xml' => $xmlAvailable ? URL::temporarySignedRoute('billing.invoices.download.xml', now()->addMinutes(10), $this->invoice) : null,
            'cdr' => $cdrAvailable ? URL::temporarySignedRoute('billing.invoices.download.cdr', now()->addMinutes(10), $this->invoice) : null,
            'pdf' => URL::temporarySignedRoute('billing.invoices.download.pdf', now()->addMinutes(10), $this->invoice),
        ];

        return view('livewire.billing.invoice-file-downloader', [
            'links' => $links,
            'xmlAvailable' => $xmlAvailable,
            'cdrAvailable' => $cdrAvailable,
        ]);
    }
}
