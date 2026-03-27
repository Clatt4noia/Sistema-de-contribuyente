<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportGuide extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_REMITENTE = 'remitente';
    public const TYPE_TRANSPORTISTA = 'transportista';

    public const DOCUMENT_TYPE_GRE_REMITENTE = '09';
    public const DOCUMENT_TYPE_GRE_TRANSPORTISTA = '31';

    public const DEFAULT_SERIES_GRE_REMITENTE = 'T001';

    public const DEFAULT_SERIES_GRE_TRANSPORTISTA = 'V001';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'type',
        'series',
        'correlative',
        'full_code',
        'issue_date',
        'issue_time',
        'document_type_code',
        'observations',
        'client_id',
        'remitente_document_type',
        'remitente_document_number',
        'remitente_ruc',
        'remitente_name',
        'destinatario_document_type',
        'destinatario_document_number',
        'destinatario_name',
        'payer_ruc',
        'payer_name',
        'transportista_ruc',
        'transportista_name',
        'order_id',
        'assignment_id',
        'truck_id',
        'secondary_truck_id',
        'driver_id',
        'vehicle_plate',
        'secondary_vehicle_plate',
        'vehicle_brand',
        'mtc_registration_number',
        'special_auth_issuer',
        'special_auth_number',
        'driver_document_number',
        'driver_document_type',
        'driver_document_type',
        'driver_name',
        'driver_last_name',
        'driver_license_number',
        'transfer_reason_code',
        'transfer_reason_description',
        'transport_mode_code',
        'scheduled_transshipment',
        'start_transport_date',
        'delivery_date',
        'gross_weight',
        'gross_weight_unit',
        'total_packages',
        'origin_ubigeo',
        'origin_address',
        'destination_ubigeo',
        'destination_address',
        'related_invoice_id',
        'related_invoice_number',
        'related_sender_guide_number',
        'additional_document_reference',
        'sunat_status',
        'sunat_ticket',
        'sunat_notes',
        'xml_path',
        'cdr_path',
        'pdf_path',
        'sent_at',
        'accepted_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'issue_time' => 'string',
        'start_transport_date' => 'date',
        'delivery_date' => 'date',
        'gross_weight' => 'decimal:3',
        'scheduled_transshipment' => 'boolean',
        'total_packages' => 'integer',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    protected $appends = [
        'display_code',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Truck::class);
    }

    public function secondaryTruck(): BelongsTo
    {
        return $this->belongsTo(Truck::class, 'secondary_truck_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function relatedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'related_invoice_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'transport_guide_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransportGuideItem::class);
    }

    protected function displayCode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->full_code ?: trim(sprintf('%s-%s', $this->series, $this->correlative), '-'),
        );
    }
}
