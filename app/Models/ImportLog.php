<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ImportLog extends Model
{
    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'status',
        'total_rows',
        'success_rows',
        'failed_rows',
        'error_message',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'error_message' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_COMPLETED_WITH_ERRORS = 'completed_with_errors';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk import yang sedang diproses
     */
    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope untuk import yang sudah selesai tanpa error
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope untuk import yang selesai dengan error
     */
    public function scopeCompletedWithErrors(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED_WITH_ERRORS);
    }

    /**
     * Scope untuk import yang gagal
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope untuk semua import yang berhasil (completed + completed_with_errors)
     */
    public function scopeAllCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_COMPLETED,
            self::STATUS_COMPLETED_WITH_ERRORS
        ]);
    }

    /**
     * Scope untuk import yang membutuhkan perhatian (failed + completed_with_errors)
     */
    public function scopeNeedsAttention(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_FAILED,
            self::STATUS_COMPLETED_WITH_ERRORS
        ]);
    }

    /**
     * Scope untuk import dalam periode tertentu
     */
    public function scopeInPeriod(Builder $query, string $period): Builder
    {
        return match ($period) {
            'today' => $query->whereDate('created_at', today()),
            'yesterday' => $query->whereDate('created_at', today()->subDay()),
            'this_week' => $query->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]),
            'this_month' => $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year),
            default => $query
        };
    }

    // Getter untuk success rate
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0.0;
        }

        return round(($this->success_rows / $this->total_rows) * 100, 2);
    }

    // Getter untuk duration
    public function getDurationAttribute(): ?string
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->started_at->diffForHumans($this->completed_at);
    }
}