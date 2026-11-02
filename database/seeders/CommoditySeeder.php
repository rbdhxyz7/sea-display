<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Commodity;
use Illuminate\Database\Seeder;

class CommoditySeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'slug');

        // [kategori, nama, harga modal, harga jual, stok (kg), batas menipis]
        $items = [
            ['ikan',   'Kakap Merah',     68000,  85000, 24, 10],
            ['udang',  'Udang Vaname',    72000,  92000, 18, 5],
            ['cumi',   'Cumi-cumi Segar', 60000,  78000, 7,  5],
            ['kerang', 'Kerang Hijau',    18000,  28000, 32, 5],
            ['ikan',   'Ikan Kembung',    36000,  45000, 20, 10],
            ['udang',  'Udang Windu',     100000, 128000, 4, 5],
            ['cumi',   'Cumi Sotong',     55000,  70000, 0,  5],
            ['kerang', 'Kerang Dara',     28000,  38000, 15, 5],
        ];

        foreach ($items as [$slug, $name, $cost, $price, $stock, $threshold]) {
            Commodity::updateOrCreate(
                ['name' => $name],
                [
                    'category_id'         => $categories[$slug],
                    'cost_price'          => $cost,
                    'selling_price'       => $price,
                    'stock'               => $stock,
                    'low_stock_threshold' => $threshold,
                ]
            );
        }
    }
}