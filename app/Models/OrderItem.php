<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'commodity_id', 'commodity_name', 'quantity', 'unit', 'unit_price', 'cost_price',
    ];

    protected $casts = [
        'quantity'   => 'float',
        'unit_price' => 'float',
        'cost_price' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function commodity()
    {
        return $this->belongsTo(Commodity::class);
    }
}