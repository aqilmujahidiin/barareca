<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // User yang melakukan import
            $table->string('file_name'); // Nama file yang diimport
            $table->string('file_path')->nullable();
            $table->string('status'); // Status: processing, completed, failed
            $table->integer('total_rows')->default(0); // Total baris yang diproses
            $table->integer('success_rows')->default(0); // Baris yang berhasil
            $table->integer('failed_rows')->default(0); // Baris yang gagal
            $table->text('error_message')->nullable(); // Pesan error jika gagal
            $table->string('error_file')->nullable(); // File export error rows
            $table->timestamp('started_at'); // Waktu mulai import
            $table->timestamp('completed_at')->nullable(); // Waktu selesai import
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('import_logs');
    }
};