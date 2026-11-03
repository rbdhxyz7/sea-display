@extends('layouts.admin')

@section('title', 'Edit Kategori')

@section('content')
<a href="{{ route('admin.kategori.index') }}" class="text-sm text-teal-700">← Kembali ke daftar</a>

<div class="mx-auto mt-4 max-w-md rounded-xl border border-slate-200 bg-white p-5 sm:p-8">
    <p class="text-xs font-bold uppercase tracking-widest text-teal-700">Perbarui data</p>
    <h1 class="mt-1 text-2xl font-semibold text-[#0b3b4a]">Edit Kategori</h1>

    <form method="POST" action="{{ route('admin.kategori.update', $category) }}" class="mt-6 space-y-4">
        @csrf @method('PUT')

        <div>
            <label for="name" class="mb-1 block text-sm font-semibold">Nama kategori</label>
            <input id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="50"
                   class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm">
            @error('name')
                <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-slate-500">Alamat filter (<span class="font-mono">{{ $category->slug }}</span>) tidak ikut berubah.</p>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.kategori.index') }}" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold">Batal</a>
            <button class="rounded-lg bg-[#0b3b4a] px-5 py-2.5 text-sm font-semibold text-white">Simpan</button>
        </div>
    </form>
</div>
@endsection