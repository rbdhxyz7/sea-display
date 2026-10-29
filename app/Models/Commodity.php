<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    protected $fillable = [
        'category_id', 'name', 'cost_price', 'selling_price',
        'stock', 'unit', 'low_stock_threshold', 'photo_path', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}