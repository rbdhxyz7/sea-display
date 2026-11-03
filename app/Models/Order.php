<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUSES = [
        'pending_payment' => 'Menunggu pembayaran',
        'paid'            => 'Dibayar',
        'processing'      => 'Diproses',
        'done'            => 'Selesai',
        'cancelled'       => 'Dibatalkan',
    ];

    protected $fillable = [
        'user_id', 'order_code', 'access_token', 'customer_name', 'customer_phone',
        'delivery_method', 'address', 'note', 'estimated_total', 'final_total',
        'status', 'payment_proof_path',
    ];

    protected $casts = [
        'estimated_total' => 'float',
        'final_total'     => 'float',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    // Ubah 0812-3456-7890 / +62 812... menjadi 62812..., atau null kalau tidak valid
    public static function normalizePhone(string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', $raw);

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return preg_match('/^628\d{8,11}$/', $digits) ? $digits : null;
    }
}