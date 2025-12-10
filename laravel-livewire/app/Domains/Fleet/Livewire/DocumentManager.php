<?php

namespace App\Domains\Fleet\Livewire;

use App\Models\Document;
use App\Models\Driver;
use App\Models\Truck;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithFileUploads;

class DocumentManager extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public string $documentableType;
    public int $documentableId;

    public array $form = [];
    public array $documents = [];
    public array $typeOptions = [];

    public $file;


    protected function rules(): array
    {
        return [
            'form.document_type' => ['required', 'string', 'max:100', Rule::in(array_keys($this->typeOptions))],
            'form.title' => ['required', 'string', 'max:150'],
            'form.issued_at' => ['nullable', 'date'],
            'form.expires_at' => ['nullable', 'date', 'after_or_equal:form.issued_at'],
            'form.notes' => ['nullable', 'string', 'max:500'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ];
    }

    public function mount(string $documentableType, int $documentableId): void
    {
        $this->documentableType = $documentableType;
        $this->documentableId = $documentableId;

        $this->typeOptions = Document::typeOptions($documentableType);
        $this->form = $this->defaultForm();

        $owner = $this->owner();
        $this->authorize('update', $owner);

        $this->refreshDocuments();
    }

    public function save(): void
    {
        $owner = $this->owner();
        $this->authorize('update', $owner);

        $validated = $this->validate();

        $title = $validated['form']['title'];
        $extension = $this->file->getClientOriginalExtension();
        $filename = Str::slug($title ?: $validated['form']['document_type']);
        $filename = trim($filename, '-') ?: 'documento';
        $filename .= '-' . now()->format('YmdHis') . '.' . strtolower($extension);

        $path = $this->file->storeAs(
            'fleet-documents/' . $this->documentableType . 's/' . $owner->getKey(),
            $filename,
            'public'
        );

        Document::create([
            'documentable_type' => $this->ownerClass(),

            'documentable_id' => $owner->getKey(),
            'document_type' => $validated['form']['document_type'],
            'title' => $title,
            'issued_at' => $validated['form']['issued_at'] ?: null,
            'expires_at' => $validated['form']['expires_at'] ?: null,
            'notes' => $validated['form']['notes'] ?: null,
            'file_path' => $path,
        ]);

        $this->reset(['file']);
        $this->form = $this->defaultForm();
        $this->refreshDocuments();

        session()->flash('message', __('Documento adjuntado correctamente.'));
    }

    public function deleteDocument(int $documentId): void
    {
        $owner = $this->owner();

        $document = Document::query()
            ->where('id', $documentId)
            ->where('documentable_type', $this->ownerClass())

            ->where('documentable_id', $this->documentableId)
            ->firstOrFail();

        $this->authorize('update', $owner);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        $this->refreshDocuments();

        session()->flash('message', __('Documento eliminado.'));
    }

    public function render()
    {
        return view('livewire.fleet.document-manager');
    }

    protected function resolveOwnerClass(string $documentableType): string
    {
        return match ($documentableType) {
            'truck' => Truck::class,
            'driver' => Driver::class,
            default => throw new InvalidArgumentException('Tipo de expediente no soportado.'),
        };
    }

    protected function owner(): Model
    {
        $class = $this->ownerClass();


        return $class::findOrFail($this->documentableId);
    }

    protected function refreshDocuments(): void
    {
        $ownerClass = $this->ownerClass();

        $this->documents = Document::query()
            ->where('documentable_type', $ownerClass)

            ->where('documentable_id', $this->documentableId)
            ->orderByRaw("CASE WHEN status = '" . Document::STATUS_EXPIRED . "' THEN 0 WHEN status = '" . Document::STATUS_WARNING . "' THEN 1 ELSE 2 END")
            ->orderBy('expires_at')
            ->get()
            ->map(function (Document $document) {
                return [
                    'id' => $document->id,
                    'title' => $document->title,
                    'type_label' => $document->type_label,
                    'status' => $document->status,
                    'status_label' => $document->status_label,
                    'issued_at' => optional($document->issued_at)->format('d/m/Y'),
                    'expires_at' => optional($document->expires_at)->format('d/m/Y'),
                    'file_url' => $document->file_url,
                ];
            })
            ->toArray();
    }

    protected function defaultForm(): array
    {
        return [
            'document_type' => array_key_first($this->typeOptions) ?: 'other',
            'title' => '',
            'issued_at' => null,
            'expires_at' => null,
            'notes' => null,
        ];
    }

    protected function ownerClass(): string
    {
        return $this->resolveOwnerClass($this->documentableType);
    }

}
