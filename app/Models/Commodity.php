<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Commodity extends Model
{
    protected $fillable = [
        'category_id', 'name', 'cost_price', 'selling_price',
        'stock', 'unit', 'low_stock_threshold', 'photo_path', 'is_active',
    ];

    protected $casts = [
        'cost_price'          => 'float',
        'selling_price'       => 'float',
        'stock'               => 'float',
        'low_stock_threshold' => 'float',
        'is_active'           => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Status dihitung dari stok, tidak disimpan di database
    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'Habis';
        }

        return $this->stock <= $this->low_stock_threshold ? 'Stok Menipis' : 'Tersedia';
    }

    // Margin = (harga jual - harga modal) / harga modal
    public function getMarginAttribute(): float
    {
        return $this->selling_price - $this->cost_price;
    }

    public function getMarginPercentAttribute(): float
    {
        return $this->cost_price > 0 ? round($this->margin / $this->cost_price * 100, 1) : 0;
    }

        public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}