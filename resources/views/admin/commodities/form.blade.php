@extends('layouts.admin')

@section('title', $commodity->exists ? 'Edit Komoditas' : 'Tambah Komoditas')

@section('content')
@php
    $isEdit = $commodity->exists;
    $input = 'w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm';
    $thresholds = $categories->mapWithKeys(fn ($c) => [$c->id => $c->slug === 'ikan' ? 10 : 5]);
@endphp

<a href="{{ route('admin.dashboard') }}#daftar" class="text-sm text-teal-700">← Kembali ke daftar</a>

<div class="mx-auto mt-4 max-w-xl rounded-xl border border-slate-200 bg-white p-5 sm:p-8">
    <p class="text-xs font-bold uppercase tracking-widest text-teal-700">{{ $isEdit ? 'Perbarui data' : 'Komoditas baru' }}</p>
    <h1 class="mt-1 text-2xl font-semibold text-[#0b3b4a]">{{ $isEdit ? 'Edit Komoditas' : 'Tambah Komoditas' }}</h1>
    <p class="text-sm text-slate-500">Lengkapi informasi komoditas yang akan ditampilkan.</p>

    @if ($errors->any())
        <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" class="mt-6 space-y-4"
          action="{{ $isEdit ? route('admin.komoditas.update', $commodity) : route('admin.komoditas.store') }}">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div>
            <label for="name" class="mb-1 block text-sm font-semibold">Nama komoditas</label>
            <input id="name" name="name" value="{{ old('name', $commodity->name) }}" required
                   placeholder="Contoh: Kakap Merah" class="{{ $input }}">
        </div>

        <div>
            <label for="category_id" class="mb-1 block text-sm font-semibold">Kategori</label>
            <select id="category_id" name="category_id" required class="{{ $input }}">
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $commodity->category_id) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="cost_price" class="mb-1 block text-sm font-semibold">Harga modal</label>
                <input id="cost_price" name="cost_price" type="number" min="0" step="any" inputmode="decimal" required
                       value="{{ old('cost_price', $commodity->cost_price) }}" class="{{ $input }}">
            </div>
            <div>
                <label for="selling_price" class="mb-1 block text-sm font-semibold">Harga jual</label>
                <input id="selling_price" name="selling_price" type="number" min="0" step="any" inputmode="decimal" required
                       value="{{ old('selling_price', $commodity->selling_price) }}" class="{{ $input }}">
            </div>
        </div>

        <div class="flex items-center justify-between rounded-lg bg-green-50 px-4 py-3 text-xs">
            <span class="text-green-800">Pratinjau margin</span>
            <span id="margin-box" class="text-sm font-semibold text-green-700">Rp 0 (0%)</span>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="col-span-2">
                <label for="stock" class="mb-1 block text-sm font-semibold">Stok</label>
                <input id="stock" name="stock" type="number" min="0" step="any" inputmode="decimal" required
                       value="{{ old('stock', $commodity->stock ?? 0) }}"
                       @if ($isEdit) readonly @endif
                       class="{{ $input }} {{ $isEdit ? 'bg-slate-100 text-slate-500' : '' }}">
                @if ($isEdit)
                    <p class="mt-1 text-xs text-slate-500">Ubah stok lewat <a href="{{ route('admin.stok.index') }}" class="text-teal-700 underline">Catat Stok</a>.</p>
                @endif
            </div>
            <div>
                <label for="unit" class="mb-1 block text-sm font-semibold">Satuan</label>
                <input id="unit" name="unit" maxlength="10" required
                       value="{{ old('unit', $commodity->unit) }}" class="{{ $input }}">
            </div>
        </div>

        <div>
            <label for="low_stock_threshold" class="mb-1 block text-sm font-semibold">Batas stok menipis</label>
            <input id="low_stock_threshold" name="low_stock_threshold" type="number" min="0" step="any" inputmode="decimal" required
                   value="{{ old('low_stock_threshold', $commodity->low_stock_threshold) }}" class="{{ $input }}">
            <p class="mt-1 text-xs text-slate-500">Stok sama dengan atau di bawah angka ini ditandai "Stok Menipis". Terisi otomatis menurut kategori, dan bisa diubah.</p>
        </div>

        <div>
            <label for="photo" class="mb-1 block text-sm font-semibold">Foto</label>
            <input id="photo" name="photo" type="file" accept="image/*"
                   class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-teal-800">
            <div class="mt-3 flex items-center gap-3 rounded-lg border border-dashed border-slate-300 p-3">
                <img id="photo-preview" src="{{ $commodity->photo_url }}" alt="Pratinjau foto"
                     class="h-16 w-16 rounded-lg object-cover {{ $commodity->photo_url ? '' : 'hidden' }}">
                <p id="photo-hint" class="text-xs text-slate-500">
                    {{ $commodity->photo_url ? 'Foto saat ini. Pilih file baru untuk menggantinya.' : 'Pilih foto dari galeri atau kamera (JPG, PNG, WebP, maksimal 8 MB).' }}
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('admin.dashboard') }}#daftar" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold">Batal</a>
            <button type="submit" class="rounded-lg bg-[#0b3b4a] px-5 py-2.5 text-sm font-semibold text-white">Simpan Komoditas</button>
        </div>
    </form>
</div>

<script>
    const byId = (id) => document.getElementById(id);

    // Pratinjau margin
    const cost = byId('cost_price'), price = byId('selling_price'), box = byId('margin-box');
    function updateMargin() {
        const c = parseFloat(cost.value) || 0, p = parseFloat(price.value) || 0;
        const m = p - c, pct = c > 0 ? (m / c) * 100 : 0;
        box.textContent = 'Rp ' + Math.round(m).toLocaleString('id-ID') + ' (' + Math.round(pct) + '%)';
        box.className = 'text-sm font-semibold ' + (m < 0 ? 'text-red-600' : 'text-green-700');
    }
    cost.addEventListener('input', updateMargin);
    price.addEventListener('input', updateMargin);
    updateMargin();

    // Batas stok menipis mengikuti kategori, selama belum diubah manual
    const category = byId('category_id'), threshold = byId('low_stock_threshold');
    const defaults = @js($thresholds);
    let touched = @js($isEdit || old('low_stock_threshold') !== null);
    threshold.addEventListener('input', () => touched = true);
    category.addEventListener('change', () => { if (!touched) threshold.value = defaults[category.value] ?? 5; });
    if (!touched) threshold.value = defaults[category.value] ?? 5;

    // Pratinjau foto
    const photo = byId('photo'), preview = byId('photo-preview'), hint = byId('photo-hint');
    photo.addEventListener('change', () => {
        const file = photo.files[0];
        if (!file) return;
        if (file.size > 8 * 1024 * 1024) {
            photo.value = '';
            hint.textContent = 'Ukuran foto melebihi 8 MB. Pilih foto lain.';
            return;
        }
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
        hint.textContent = file.name;
    });
</script>
@endsection