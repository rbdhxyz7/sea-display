<?php

namespace App\Services;

use App\Models\Commodity;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function record(
        Commodity $commodity,
        string $source,
        float $quantity,
        ?float $unitPrice = null,
        ?string $note = null,
        ?Carbon $movedAt = null,
        ?int $userId = null,
        ?int $orderId = null,
        ?float $costPrice = null,
    ): StockMovement {
        $type = in_array($source, ['opening', 'purchase', 'order_return'], true) ? 'in' : 'out';

        return DB::transaction(function () use ($commodity, $source, $type, $quantity, $unitPrice, $note, $movedAt, $userId, $orderId, $costPrice) {
            // Kunci baris komoditas agar dua pencatatan bersamaan tidak saling menimpa
            $locked = Commodity::whereKey($commodity->id)->lockForUpdate()->firstOrFail();

            if ($type === 'out' && $quantity > $locked->stock) {
                throw ValidationException::withMessages([
                    'quantity' => "Stok {$locked->name} hanya tersisa " . (float) $locked->stock . " {$locked->unit}.",
                ]);
            }

            $locked->stock = round($type === 'in' ? $locked->stock + $quantity : $locked->stock - $quantity, 2);

            if ($source === 'purchase' && $unitPrice !== null) {
                $locked->cost_price = $unitPrice; // harga modal = harga kulakan terbaru
            }

            $locked->save();

            return StockMovement::create([
                'commodity_id' => $locked->id,
                'user_id'      => $userId,
                'order_id'     => $orderId,
                'type'         => $type,
                'source'       => $source,
                'quantity'     => $quantity,
                'unit_price'   => $unitPrice,
                'cost_price'   => $costPrice ?? $locked->cost_price,
                'note'         => $note,
                'moved_at'     => $movedAt ?? now(),
            ]);
        });
    }
}