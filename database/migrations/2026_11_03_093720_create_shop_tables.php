<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('whatsapp_number', 20);
            $table->string('qris_image_path')->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('account_number', 30)->nullable();
            $table->string('account_holder', 100)->nullable();
            $table->timestamps();
        });

        DB::table('store_settings')->insert([
            'whatsapp_number' => config('shop.whatsapp', '6281234567890'),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_code', 20)->nullable()->unique();
            $table->string('access_token', 64)->unique();
            $table->string('customer_name', 100);
            $table->string('customer_phone', 20);
            $table->string('delivery_method', 10);
            $table->text('address')->nullable();
            $table->string('note', 200)->nullable();
            $table->decimal('estimated_total', 12, 2);
            $table->decimal('final_total', 12, 2)->nullable();
            $table->string('status', 20)->default('pending_payment')->index();
            $table->string('payment_proof_path')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commodity_id')->constrained()->restrictOnDelete();
            $table->string('commodity_name');
            $table->decimal('quantity', 10, 2);
            $table->string('unit', 10);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('cost_price', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('store_settings');
    }
};