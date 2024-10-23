<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'import_id',
        'status',
        'row_data',
        'model_id',
        'error_message'
    ];

    protected $casts = [
        'row_data' => 'array',
    ];

    /**
     * Scope untuk mendapatkan log berdasarkan import_id
     */
    public function scopeForImport($query, $importId)
    {
        return $query->where('import_id', $importId);
    }

    /**
     * Scope untuk mendapatkan data yang sukses
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope untuk mendapatkan data yang gagal
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Get related model (misalnya User) jika import sukses
     */
    public function relatedModel()
    {
        if (!$this->model_id) {
            return null;
        }

        // Anda bisa menyesuaikan ini sesuai dengan model yang di-import
        return $this->belongsTo(User::class, 'model_id');
    }

    /**
     * Get summary untuk specific import
     */
    public static function getImportSummary($importId)
    {
        $total = self::forImport($importId)->count();
        $success = self::forImport($importId)->successful()->count();
        $failed = self::forImport($importId)->failed()->count();

        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'errors' => self::forImport($importId)
                ->failed()
                ->pluck('error_message')
                ->toArray()
        ];
    }
}