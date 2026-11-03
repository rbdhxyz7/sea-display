<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'whatsapp_number', 'qris_image_path', 'bank_name', 'account_number', 'account_holder',
    ];

    public static function current(): self
    {
        return static::first() ?? static::create(['whatsapp_number' => config('shop.whatsapp')]);
    }

    public function getQrisUrlAttribute(): ?string
    {
        return $this->qris_image_path ? asset('storage/' . $this->qris_image_path) : null;
    }

    public function getHasBankAttribute(): bool
    {
        return filled($this->bank_name) && filled($this->account_number);
    }
}