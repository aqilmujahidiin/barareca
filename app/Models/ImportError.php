<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportError extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'import_log_id',
        'row_number',
        'row_data',
        'errors',
        'error_message'
    ];

    protected $casts = [
        'row_data' => 'array',
        'errors' => 'array'
    ];

    /**
     * Relasi ke ImportLog
     */
    public function importLog()
    {
        return $this->belongsTo(ImportLog::class);
    }

    /**
     * Get error messages dalam format yang readable
     */
    public function getFormattedErrorsAttribute()
    {
        if (empty($this->errors)) {
            return [];
        }

        $formatted = [];
        foreach ($this->errors as $column => $error) {
            $formatted[] = "Kolom '{$column}': {$error}";
        }

        return $formatted;
    }

    /**
     * Get specific column value from row_data
     */
    public function getColumnValue($column)
    {
        return $this->row_data[$column] ?? null;
    }

    /**
     * Get error for specific column
     */
    public function getColumnError($column)
    {
        return $this->errors[$column] ?? null;
    }

    /**
     * Check if specific column has error
     */
    public function hasErrorInColumn($column)
    {
        return isset($this->errors[$column]);
    }
}