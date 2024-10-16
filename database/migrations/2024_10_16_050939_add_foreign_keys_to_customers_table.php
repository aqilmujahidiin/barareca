<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('customer_service_id')->constrained();
            $table->foreignId('advertiser_id')->constrained();
            $table->foreignId('operator_id')->constrained();
            $table->foreignId('status_customer_id')->constrained('status_customers');
            $table->foreignId('company_id')->constrained();
            $table->foreignId('divisi_id')->constrained();
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['customer_service_id']);
            $table->dropForeign(['advertiser_id']);
            $table->dropForeign(['operator_id']);
            $table->dropForeign(['status_customer_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['divisi_id']);
            $table->dropForeign(['product_id']);
        });
    }
};