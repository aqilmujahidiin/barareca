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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('no_telepon')->nullable();
            $table->string('nama_pelanggan')->nullable();
            $table->string('nama_produk');
            $table->integer('quantity');
            $table->string('alamat_pengirim')->nullable();
            $table->string('id_pelacakan')->nullable();
            $table->string('status_granular')->nullable();
            $table->string('nama_pengirim')->nullable();
            $table->string('kontak_pengirim')->nullable();
            $table->string('kode_pos_pengirim')->nullable();
            $table->decimal('cash_on_delivery', 10, 2)->default(0);
            $table->decimal('transfer', 10, 2)->default(0);
            $table->string('alamat_penerima')->nullable();
            $table->string('customer_service');
            $table->string('advertiser');
            $table->string('inp')->nullable();
            $table->decimal('ongkos_kirim', 10, 2);
            $table->decimal('potongan_ongkos_kirim', 10, 2)->default(0);
            $table->decimal('potongan_lain_1', 10, 2)->default(0);
            $table->decimal('potongan_lain_2', 10, 2)->default(0);
            $table->decimal('potongan_lain_3', 10, 2)->default(0);
            $table->string('status_customer');
            $table->string('alamat_penerima_2')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('no_invoice')->nullable();
            $table->string('keterangan_promo')->nullable();
            $table->string('company');
            $table->string('divisi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
