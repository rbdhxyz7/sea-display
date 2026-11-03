<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Commodity;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $active = Commodity::where('is_active', true)->get();

        $stats = [
            'total'  => $active->count(),
            'stock'  => $active->sum('stock'),
            'low'    => $active->filter(fn ($c) => $c->stock <= $c->low_stock_threshold)->count(),
            'margin' => round($active->avg('margin_percent') ?? 0),
        ];

        $commodities = Commodity::with('category')
            ->where('is_active', true)
            ->when($request->q, fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
            ->when($request->kategori, fn ($query, $slug) => $query->whereHas(
                'category', fn ($c) => $c->where('slug', $slug)
            ))
            ->orderBy('name')
            ->get();

        return view('admin.dashboard', [
            'stats'       => $stats,
            'commodities' => $commodities,
            'categories'  => Category::orderBy('id')->get(),
            'today'       => now()->locale('id')->translatedFormat('l, d F Y'),
        ]);
    }
}