<?php
// app/Models/ImportLog.php

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
        'error_file',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'error_message' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_COMPLETED_WITH_ERRORS = 'completed_with_errors';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCompletedWithErrors(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED_WITH_ERRORS);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeAllCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_COMPLETED,
            self::STATUS_COMPLETED_WITH_ERRORS
        ]);
    }

    public function scopeNeedsAttention(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_FAILED,
            self::STATUS_COMPLETED_WITH_ERRORS
        ]);
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->total_rows === 0) {
            return 0.0;
        }
        return round(($this->success_rows / $this->total_rows) * 100, 2);
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->completed_at) {
            return null;
        }
        return $this->started_at->diffForHumans($this->completed_at);
    }
}