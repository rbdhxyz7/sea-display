@extends('layouts.admin')

@section('title', 'Kelola Kategori')

@section('content')
<div>
    <p class="text-xs font-bold uppercase tracking-widest text-teal-700">Pengaturan katalog</p>
    <h1 class="mt-1 text-2xl font-semibold text-[#0b3b4a]">Kelola Kategori</h1>
    <p class="text-sm text-slate-500">Kategori muncul sebagai tombol filter di katalog.</p>
</div>

@error('delete')
    <p class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</p>
@enderror

<form method="POST" action="{{ route('admin.kategori.store') }}"
      class="mt-6 rounded-xl border border-slate-200 bg-white p-4 sm:p-6">
    @csrf
    <label for="name" class="mb-1 block text-sm font-semibold">Tambah kategori baru</label>
    <div class="flex gap-2">
        <input id="name" name="name" value="{{ old('name') }}" required maxlength="50"
               placeholder="Contoh: Kepiting"
               class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm">
        <button class="rounded-lg bg-[#0b3b4a] px-5 py-3 text-sm font-semibold text-white">Tambah</button>
    </div>
    @error('name')
        <p class="mt-2 text-sm text-red-600" role="alert">{{ $message }}</p>
    @enderror
</form>

<section class="mt-6 rounded-xl border border-slate-200 bg-white">
    <div class="divide-y divide-slate-100">
        @foreach ($categories as $cat)
            <div class="flex flex-wrap items-center justify-between gap-3 p-4 sm:px-6">
                <div>
                    <p class="font-semibold">{{ $cat->name }}</p>
                    <p class="text-xs text-slate-500">
                        <span class="font-mono">{{ $cat->slug }}</span> · {{ $cat->commodities_count }} komoditas
                    </p>
                </div>
                <div class="flex gap-1.5">
                    <a href="{{ route('admin.kategori.edit', $cat) }}"
                       class="rounded-lg border border-teal-200 px-3 py-1.5 text-xs font-semibold text-teal-700">Edit</a>
                    <form method="POST" action="{{ route('admin.kategori.destroy', $cat) }}"
                          onsubmit="return confirm(@js('Hapus kategori ' . $cat->name . '?'))">
                        @csrf @method('DELETE')
                        <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600">Hapus</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection