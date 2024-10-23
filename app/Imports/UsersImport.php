<?php

namespace App\Imports;

use App\Models\User;
use App\Models\UserImport;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Throwable;

class UsersImport implements
    ToModel,
    WithHeadingRow,
    WithChunkReading,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithBatchInserts,
    WithEvents
{
    private $importId;
    private $rows = 0;
    private $successCount = 0;
    private $failureCount = 0;
    private $errors = [];

    public function __construct($importId)
    {
        $this->importId = $importId;
    }

    public function model(array $row)
    {
        $this->rows++;

        try {
            $user = User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'username' => $row['username'],
                'password' => Hash::make($row['password'] ?? 'password'),
            ]);

            $this->successCount++;
            $this->updateImportProgress();

            return $user;
        } catch (Throwable $e) {
            $this->failureCount++;
            $this->errors[] = "Row {$this->rows}: " . $e->getMessage();
            $this->updateImportProgress();
            return null;
        }
    }

    private function updateImportProgress()
    {
        UserImport::where('id', $this->importId)->update([
            'processed_rows' => $this->rows,
            'successful_rows' => $this->successCount,
            'failed_rows' => $this->failureCount,
            'error_messages' => !empty($this->errors) ? json_encode($this->errors) : null
        ]);
    }

    public function getImportSummary()
    {
        return [
            'processed_rows' => $this->rows,
            'successful_rows' => $this->successCount,
            'failed_rows' => $this->failureCount,
            'error_messages' => $this->errors
        ];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],
        ];
    }

    public function onError(Throwable $e)
    {
        $this->failureCount++;
        $this->errors[] = "Row {$this->rows}: " . $e->getMessage();
        $this->updateImportProgress();
    }

    public function onFailure(...$failures)
    {
        foreach ($failures as $failure) {
            $this->failureCount++;
            $this->errors[] = "Row {$this->rows}: " . implode(', ', $failure->errors());
        }
        $this->updateImportProgress();
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                UserImport::where('id', $this->importId)->update([
                    'status' => 'processing'
                ]);
            },
            AfterImport::class => function (AfterImport $event) {
                UserImport::where('id', $this->importId)->update([
                    'status' => $this->failureCount > 0 ? 'completed_with_errors' : 'completed',
                    'completed_at' => Carbon::now(),
                    'error_messages' => !empty($this->errors) ? json_encode($this->errors) : null
                ]);
            }
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
