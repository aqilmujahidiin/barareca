<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('import_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_log_id')->constrained()->cascadeOnDelete();
            $table->integer('row_number');
            $table->json('row_data'); // Data mentah dari excel
            $table->json('errors'); // Error messages per kolom
            $table->text('error_message'); // Error message utama
            $table->timestamps();
            $table->softDeletes();

            // Index untuk performa query
            $table->index(['import_log_id', 'row_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('import_errors');
    }
};