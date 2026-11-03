<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    public const SOURCES = [
        'opening'      => 'Saldo awal',
        'purchase'     => 'Stok masuk (kulakan)',
        'sale'         => 'Penjualan langsung',
        'order'        => 'Pesanan online',
        'order_return' => 'Pembatalan pesanan',
        'waste'        => 'Susut / rusak',
    ];

    protected $fillable = [
        'commodity_id', 'user_id', 'order_id', 'type', 'source',
        'quantity', 'unit_price', 'cost_price', 'note', 'moved_at',
    ];

    protected $casts = [
        'quantity'   => 'float',
        'unit_price' => 'float',
        'cost_price' => 'float',
        'moved_at'   => 'datetime',
    ];

    public function commodity()
    {
        return $this->belongsTo(Commodity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Penjualan = penjualan langsung + pesanan online yang tidak dibatalkan
    public function scopeSales($query)
    {
        return $query->where(function ($q) {
            $q->where('stock_movements.source', 'sale')
              ->orWhere(function ($o) {
                  $o->where('stock_movements.source', 'order')
                    ->whereHas('order', fn ($r) => $r->where('status', '!=', 'cancelled'));
              });
        });
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }
}