<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commodity_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('order_id')->nullable(); // relasi ditambahkan di Fase 3
            $table->string('type', 3);       // in / out
            $table->string('source', 20);    // opening, purchase, sale, waste
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->string('note')->nullable();
            $table->dateTime('moved_at');
            $table->timestamps();

            $table->index(['commodity_id', 'moved_at']);
            $table->index(['type', 'source', 'moved_at']);
        });

        // Stok yang sudah ada dicatat sebagai saldo awal, supaya riwayat dan stok sinkron
        DB::table('commodities')->where('stock', '>', 0)->get()->each(function ($c) {
            DB::table('stock_movements')->insert([
                'commodity_id' => $c->id,
                'type'         => 'in',
                'source'       => 'opening',
                'quantity'     => $c->stock,
                'unit_price'   => $c->cost_price,
                'cost_price'   => $c->cost_price,
                'note'         => 'Saldo awal',
                'moved_at'     => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};