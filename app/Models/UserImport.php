<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;

class UserImport extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'status',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'error_messages',
        'user_id',
        'completed_at'
    ];

    protected $attributes = [
        'status' => 'pending',
        'processed_rows' => 0,
        'successful_rows' => 0,
        'failed_rows' => 0
    ];

    protected $casts = [
        'error_messages' => 'array',
        'completed_at' => 'datetime'
    ];

    /**
     * Get the user that created the import.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): EloquentBelongsTo
    {
        return $this->belongsTo(User::class);
    }
}