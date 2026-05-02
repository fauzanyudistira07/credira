<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('midtrans_order_id', 100)->nullable()->after('kode_pembayaran');
            $table->string('midtrans_transaction_id', 100)->nullable()->after('midtrans_order_id');
            $table->string('midtrans_snap_token', 120)->nullable()->after('midtrans_transaction_id');
            $table->text('midtrans_redirect_url')->nullable()->after('midtrans_snap_token');
            $table->string('midtrans_payment_type', 80)->nullable()->after('midtrans_redirect_url');
            $table->string('midtrans_status_code', 10)->nullable()->after('midtrans_payment_type');
            $table->json('midtrans_payload')->nullable()->after('midtrans_status_code');

            $table->index('midtrans_order_id');
            $table->index('midtrans_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropIndex(['midtrans_order_id']);
            $table->dropIndex(['midtrans_transaction_id']);
            $table->dropColumn([
                'midtrans_order_id',
                'midtrans_transaction_id',
                'midtrans_snap_token',
                'midtrans_redirect_url',
                'midtrans_payment_type',
                'midtrans_status_code',
                'midtrans_payload',
            ]);
        });
    }
};
