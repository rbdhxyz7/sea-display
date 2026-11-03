@extends('layouts.admin')

@section('title', 'Akun Saya')

@section('content')
@php $input = 'w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm'; @endphp

<p class="text-xs font-bold uppercase tracking-widest text-teal-700">Akun</p>
<h1 class="mt-1 text-2xl font-semibold text-[#0b3b4a]">Akun Saya</h1>

<div class="mt-4 max-w-xl rounded-xl border border-slate-200 bg-white p-5 text-sm sm:p-8">
    <dl class="grid gap-3 sm:grid-cols-2">
        <div><dt class="text-xs text-slate-500">Nama</dt><dd class="font-semibold">{{ $user->name }}</dd></div>
        <div><dt class="text-xs text-slate-500">Email</dt><dd class="font-semibold">{{ $user->email }}</dd></div>
        <div><dt class="text-xs text-slate-500">Peran</dt><dd class="font-semibold">{{ $user->role === 'owner' ? 'Pemilik' : 'Staf' }}</dd></div>
    </dl>
</div>

<form method="POST" action="{{ route('admin.akun.update') }}"
      class="mt-6 max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-5 sm:p-8">
    @csrf @method('PUT')
    <h2 class="text-lg font-medium text-[#0b3b4a]">Ganti password</h2>

    @if ($errors->any())
        <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div>
        <label for="current_password" class="mb-1 block text-sm font-semibold">Password saat ini</label>
        <input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="{{ $input }}">
    </div>
    <div>
        <label for="password" class="mb-1 block text-sm font-semibold">Password baru</label>
        <input id="password" name="password" type="password" autocomplete="new-password" required class="{{ $input }}">
        <p class="mt-1 text-xs text-slate-500">Minimal 8 karakter, berisi huruf dan angka.</p>
    </div>
    <div>
        <label for="password_confirmation" class="mb-1 block text-sm font-semibold">Ulangi password baru</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="{{ $input }}">
    </div>

    <div class="flex justify-end">
        <button class="rounded-lg bg-[#0b3b4a] px-5 py-2.5 text-sm font-semibold text-white">Simpan Password</button>
    </div>
</form>
@endsection