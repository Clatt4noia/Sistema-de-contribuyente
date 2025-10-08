<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'client_id',
        'document_type',
        'series',
        'correlative',
        'invoice_number',
        'issue_date',
        'due_date',
        'ruc_emisor',
        'ruc_receptor',
        'currency',
        'subtotal',
        'tax',
        'total',
        'taxable_amount',
        'unaffected_amount',
        'exempt_amount',
        'status',
        'notes',
        'hash',
        'xml_path',
        'cdr_path',
        'metadata',
        'sunat_status',
        'sunat_sent_at',
        'sunat_response_message',
        'sunat_ticket',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'unaffected_amount' => 'decimal:2',
        'exempt_amount' => 'decimal:2',
        'sunat_sent_at' => 'datetime',
        'metadata' => 'array',

    ];

    protected $appends = [
        'balance',
        'numero_completo',
    ];

    protected $attributes = [
        'sunat_status' => 'pendiente',
        'metadata' => '[]',
    ];


    public function scopeAceptadas($query)
    {
        return $query->where('sunat_status', 'aceptado');
    }

    public function scopePendientes($query)
    {
        return $query->whereIn('sunat_status', ['pendiente', 'observado']);
    }

    public function scopeRechazadas($query)
    {
        return $query->where('sunat_status', 'rechazado');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(InvoiceAudit::class);
    }

    public function sunatLogs(): HasMany
    {
        return $this->hasMany(SunatLog::class);
    }

    public function latestSunatLog(): HasOne
    {
        return $this->hasOne(SunatLog::class)->latestOfMany();
    }

    protected function balance(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $paid = $this->getAttribute('payments_sum_amount');

                if ($paid === null && $this->relationLoaded('payments')) {
                    $paid = $this->payments->sum('amount');
                }

                if ($paid === null) {
                    $paid = $this->payments()->sum('amount');
                }

                return round(max((float) $this->total - (float) $paid, 0), 2);
            },
        );
    }

    protected function numeroCompleto(): Attribute
    {
        return Attribute::make(
            get: fn () => trim(sprintf('%s-%s', $this->series, $this->correlative), '-'),
        );
    }

    public function getFormattedIssueDateAttribute(): ?string
    {
        return $this->issue_date instanceof Carbon
            ? $this->issue_date->format('d/m/Y')
            : null;

    }
}
