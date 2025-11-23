<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'category',
        'amount',
        'occurred_on',
        'description',
    ];

    protected $casts = [
        'occurred_on' => 'date',
        'amount' => 'decimal:2',
    ];

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSearchTerm(Builder $query, string $term): Builder
    {
        return $query->when($term !== '', function (Builder $builder) use ($term) {
            $builder->where(function (Builder $subQuery) use ($term) {
                $subQuery->where('category', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%');
            });
        });
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $query->when($type && $type !== 'all', fn (Builder $builder) => $builder->where('type', $type));
    }

    public function scopeForYear(Builder $query, ?string $year): Builder
    {
        return $query->when($year, fn (Builder $builder) => $builder->whereYear('occurred_on', $year));
    }

    public function scopeForMonth(Builder $query, ?string $month): Builder
    {
        return $query->when($month, fn (Builder $builder) => $builder->whereMonth('occurred_on', $month));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
