<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('data_customers', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('nama_pelanggan')->nullable();
            $table->string('no_telepon')->nullable();
            $table->integer('quantity')->nullable();
            $table->text('alamat_pengirim')->nullable();
            $table->string('id_pelacakan')->nullable();
            $table->enum('status_granular', ['pending', 'shipped']);
            $table->string('nama_pengirim')->nullable();
            $table->string('kontak_pengirim')->nullable();
            $table->string('kode_pos_pengirim')->nullable();
            $table->enum('metode_pembayaran', ['cod', 'transfer']);
            $table->decimal('total_pembayaran', 12, 2);
            $table->text('alamat_penerima')->nullable();
            $table->text('alamat_penerima_2')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('no_invoice')->nullable();
            $table->text('keterangan_promo')->nullable();
            $table->text('keterangan_issue')->nullable();
            $table->decimal('ongkos_kirim', 10, 2)->nullable();
            $table->decimal('potongan_ongkos_kirim', 10, 2)->nullable();
            $table->decimal('potongan_lain_1', 10, 2)->nullable();
            $table->decimal('potongan_lain_2', 10, 2)->nullable();
            $table->decimal('potongan_lain_3', 10, 2)->nullable();
            $table->string('customer_service');
            $table->string('advertiser');
            $table->foreignId('operator_id')->constrained('users')->onDelete('cascade');
            $table->string('status_customer')->nullable();
            $table->string('company');
            $table->string('divisi');
            $table->string('nama_produk');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('operator_id');
            $table->index(['operator_id', 'tanggal']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_customers');
    }
};