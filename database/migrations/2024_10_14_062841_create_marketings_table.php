<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('marketings', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->decimal('budget_iklan', 15, 2)->nullable();
            $table->integer('lead')->nullable();
            $table->integer('closing')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('omset', 15, 2)->nullable();
            $table->decimal('target_omset', 15, 2)->nullable();
            $table->string('produk');
            $table->string('divisi');
            $table->string('company');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketings');
    }
};
