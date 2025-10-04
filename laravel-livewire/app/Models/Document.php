<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    public const STATUS_VALID = 'valid';
    public const STATUS_WARNING = 'warning';
    public const STATUS_EXPIRED = 'expired';

    protected const BASE_TYPE_LABELS = [
        'soat' => 'SOAT',
        'insurance' => 'Póliza de seguro',
        'technical_inspection' => 'Revisión técnica',
        'operation_permit' => 'Permiso de operación',
        'gps_certificate' => 'Certificado GPS',
        'license' => 'Licencia de conducir',
        'medical_exam' => 'Certificado médico',
        'training' => 'Capacitación',
        'background_check' => 'Antecedentes',
        'other' => 'Otro',
    ];

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'document_type',
        'title',
        'file_path',
        'issued_at',
        'expires_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expires_at' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (Document $document) {
            $document->status = $document->computeStatus();
        });
    }

    public static function typeOptions(string $documentableType): array
    {
        return match ($documentableType) {
            'truck' => [
                'soat' => self::BASE_TYPE_LABELS['soat'],
                'insurance' => self::BASE_TYPE_LABELS['insurance'],
                'technical_inspection' => self::BASE_TYPE_LABELS['technical_inspection'],
                'operation_permit' => self::BASE_TYPE_LABELS['operation_permit'],
                'gps_certificate' => self::BASE_TYPE_LABELS['gps_certificate'],
                'other' => self::BASE_TYPE_LABELS['other'],
            ],
            'driver' => [
                'license' => self::BASE_TYPE_LABELS['license'],
                'medical_exam' => self::BASE_TYPE_LABELS['medical_exam'],
                'training' => self::BASE_TYPE_LABELS['training'],
                'background_check' => self::BASE_TYPE_LABELS['background_check'],
                'other' => self::BASE_TYPE_LABELS['other'],
            ],
            default => [
                'other' => self::BASE_TYPE_LABELS['other'],
            ],
        };
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function computeStatus(): string
    {
        if (! $this->expires_at instanceof Carbon) {
            return self::STATUS_VALID;
        }

        if ($this->expires_at->isPast()) {
            return self::STATUS_EXPIRED;
        }

        if ($this->expires_at->diffInDays(now(), false) <= 30) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_VALID;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::BASE_TYPE_LABELS[$this->document_type] ?? ucfirst(str_replace('_', ' ', (string) $this->document_type));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_EXPIRED => __('Vencido'),
            self::STATUS_WARNING => __('Por vencer'),
            default => __('Vigente'),
        };
    }

    public function getOwnerLabelAttribute(): string
    {
        $owner = $this->documentable;

        if ($owner instanceof Truck) {
            return __('Camión :plate', ['plate' => $owner->plate_number ?? $owner->getKey()]);
        }

        if ($owner instanceof Driver) {
            return __('Chofer :name', ['name' => $owner->full_name ?? $owner->getKey()]);
        }

        return (string) ($owner?->name ?? $this->documentable_id);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->file_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->file_path);
    }
}
