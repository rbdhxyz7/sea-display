@extends('layouts.admin')

@section('title', 'Kelola Pengguna')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <p class="text-xs font-bold uppercase tracking-widest text-teal-700">Pengaturan</p>
        <h1 class="mt-1 text-2xl font-semibold text-[#0b3b4a]">Kelola Pengguna</h1>
        <p class="text-sm text-slate-500">Akun yang bisa masuk ke panel admin.</p>
    </div>
    <a href="{{ route('admin.pengguna.create') }}" class="rounded-lg bg-[#0b3b4a] px-4 py-2.5 text-sm font-semibold text-white">+ Tambah Pengguna</a>
</div>

<section class="mt-6 rounded-xl border border-slate-200 bg-white">
    <div class="divide-y divide-slate-100">
        @foreach ($users as $u)
            @php
                $self = $u->id === auth()->id();
                $active = (bool) $u->is_active;
            @endphp
            <div class="flex flex-wrap items-center justify-between gap-3 p-4 sm:px-6">
                <div class="min-w-0">
                    <p class="font-semibold">
                        {{ $u->name }}
                        @if ($self) <span class="ml-1 text-xs font-normal text-slate-400">(Anda)</span> @endif
                    </p>
                    <p class="truncate text-xs text-slate-500">{{ $u->email }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $u->role === 'owner' ? 'bg-teal-50 text-teal-800' : 'bg-slate-100 text-slate-700' }}">
                        {{ $u->role === 'owner' ? 'Pemilik' : 'Staf' }}
                    </span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700' }}">
                        {{ $active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    <a href="{{ route('admin.pengguna.edit', $u) }}"
                       class="rounded-lg border border-teal-200 px-3 py-1.5 text-xs font-semibold text-teal-700">Edit</a>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endsection