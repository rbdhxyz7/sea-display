<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Commodity;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('id')->get();

        $commodities = Commodity::with('category')
            ->where('is_active', true)
            ->when($request->q, fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
            ->when($request->kategori, fn ($query, $slug) => $query->whereHas(
                'category', fn ($c) => $c->where('slug', $slug)
            ))
            ->orderBy('name')
            ->get();

        return view('catalog.index', compact('categories', 'commodities'));
    }
}