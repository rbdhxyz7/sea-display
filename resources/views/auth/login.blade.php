<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Admin – Sea Display</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">

    <div class="relative hidden flex-col justify-between bg-gradient-to-br from-[#0a2f3d] to-[#0e6a73] p-10 text-white lg:flex">
        <a href="{{ route('catalog') }}" class="text-sm font-semibold">← Kembali ke katalog</a>
        <div>
            <p class="mb-4 text-lg font-bold">≈ Sea Display</p>
            <p class="text-3xl font-semibold leading-snug">
                “Kelola komoditas dengan mudah, layani pelanggan dengan lebih baik.”
            </p>
            <p class="mt-4 text-sm text-cyan-100">— Bu Desy, Pemilik Sea Display</p>
        </div>
    </div>

    <div class="flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-sm">
            <a href="{{ route('catalog') }}" class="mb-6 block text-sm text-teal-700 lg:hidden">← Kembali ke katalog</a>

            <p class="text-xs font-bold tracking-widest text-teal-700">PORTAL PENGELOLA</p>
            <h1 class="mt-2 text-3xl font-semibold leading-tight text-[#0b3b4a]">Selamat datang,<br>Bu Desy</h1>
            <p class="mt-2 text-sm text-slate-500">Masuk untuk mengelola katalog dan stok komoditas.</p>

            <form method="POST" action="{{ url('/login') }}" class="mt-8 space-y-4">
                @csrf

                @error('email')
                    <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">{{ $message }}</p>
                @enderror

                <div>
                    <label for="email" class="mb-1 block text-sm font-semibold">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="nama@email.com"
                           class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm">
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-semibold">Password</label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required
                               placeholder="Masukkan password"
                               class="w-full rounded-lg border border-slate-200 bg-white px-4 py-3 pr-20 text-sm">
                        <button type="button" id="toggle-pw"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-teal-700">
                            Tampilkan
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full rounded-lg bg-[#0b3b4a] py-3 text-sm font-semibold text-white">
                    Masuk →
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const pw = document.getElementById('password');
    const btn = document.getElementById('toggle-pw');
    btn.addEventListener('click', () => {
        const hidden = pw.type === 'password';
        pw.type = hidden ? 'text' : 'password';
        btn.textContent = hidden ? 'Sembunyikan' : 'Tampilkan';
    });
</script>
</body>
</html>