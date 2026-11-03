@extends('layouts.admin')

@section('title', $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
@php
    $isEdit = $user->exists;
    $isSelf = $isEdit && $user->id === auth()->id();
    $input = 'w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm';
    $role = old('role', $user->role ?: 'staff');
    $activeChecked = session()->hasOldInput()
        ? (bool) old('is_active')
        : ($isEdit ? (bool) $user->is_active : true);
@endphp

<a href="{{ route('admin.pengguna.index') }}" class="text-sm text-teal-700">← Kembali ke daftar</a>

<div class="mx-auto mt-4 max-w-xl rounded-xl border border-slate-200 bg-white p-5 sm:p-8">
    <p class="text-xs font-bold uppercase tracking-widest text-teal-700">{{ $isEdit ? 'Perbarui akun' : 'Akun baru' }}</p>
    <h1 class="mt-1 text-2xl font-semibold text-[#0b3b4a]">{{ $isEdit ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h1>

    @if ($errors->any())
        <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" class="mt-6 space-y-4"
          action="{{ $isEdit ? route('admin.pengguna.update', $user) : route('admin.pengguna.store') }}">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div>
            <label for="name" class="mb-1 block text-sm font-semibold">Nama</label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="100" class="{{ $input }}">
        </div>

        <div>
            <label for="email" class="mb-1 block text-sm font-semibold">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="150" class="{{ $input }}">
        </div>

        <div>
            <label for="role" class="mb-1 block text-sm font-semibold">Peran</label>
            <select id="role" name="role" @disabled($isSelf) class="{{ $input }} {{ $isSelf ? 'bg-slate-100 text-slate-500' : '' }}">
                <option value="staff" @selected($role === 'staff')>Staf (operasional harian)</option>
                <option value="owner" @selected($role === 'owner')>Pemilik (akses penuh)</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">
                @if ($isSelf)
                    Peran dan status akunmu sendiri tidak bisa diubah, supaya selalu ada pemilik yang aktif.
                @else
                    Staf tidak bisa membuka Laporan, Pengaturan Toko, dan Kelola Pengguna.
                @endif
            </p>
        </div>

        <div>
            <label for="password" class="mb-1 block text-sm font-semibold">
                {{ $isEdit ? 'Password baru (kosongkan jika tidak diganti)' : 'Password awal' }}
            </label>
            <input id="password" name="password" type="password" autocomplete="new-password"
                   @required(! $isEdit) class="{{ $input }}">
            <p class="mt-1 text-xs text-slate-500">Minimal 8 karakter, berisi huruf dan angka.</p>
        </div>

        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-semibold">Ulangi password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   @required(! $isEdit) class="{{ $input }}">
        </div>

        @if ($isEdit)
            <label class="flex items-center gap-2 text-sm {{ $isSelf ? 'text-slate-400' : '' }}">
                <input type="checkbox" name="is_active" value="1" @checked($activeChecked) @disabled($isSelf)>
                Akun aktif (bisa masuk ke panel admin)
            </label>
        @endif

        <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('admin.pengguna.index') }}" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold">Batal</a>
            <button class="rounded-lg bg-[#0b3b4a] px-5 py-2.5 text-sm font-semibold text-white">Simpan</button>
        </div>
    </form>
</div>
@endsection