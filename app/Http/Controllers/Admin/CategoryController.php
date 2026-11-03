<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    private const MESSAGES = [
        'required' => ':attribute wajib diisi.',
        'max'      => ':attribute terlalu panjang.',
        'unique'   => ':attribute sudah dipakai kategori lain.',
    ];

    public function index()
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('commodities')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(
            ['name' => ['required', 'string', 'max:50', 'unique:categories,name']],
            self::MESSAGES,
            ['name' => 'Nama kategori']
        );

        $slug = Str::slug($data['name']);

        if ($slug === '' || Category::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages(['name' => 'Nama kategori terlalu mirip dengan kategori yang sudah ada.']);
        }

        Category::create(['name' => $data['name'], 'slug' => $slug]);

        return redirect()->route('admin.kategori.index')->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate(
            ['name' => ['required', 'string', 'max:50', Rule::unique('categories', 'name')->ignore($category->id)]],
            self::MESSAGES,
            ['name' => 'Nama kategori']
        );

        $category->update(['name' => $data['name']]);

        return redirect()->route('admin.kategori.index')->with('status', 'Nama kategori diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->commodities()->exists()) {
            return redirect()->route('admin.kategori.index')
                ->withErrors(['delete' => "Kategori \"{$category->name}\" masih dipakai komoditas, jadi tidak bisa dihapus. Pindahkan atau hapus komoditasnya dulu."]);
        }

        $category->delete();

        return redirect()->route('admin.kategori.index')->with('status', 'Kategori dihapus.');
    }
}