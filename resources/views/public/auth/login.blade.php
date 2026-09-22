<x-layouts.public title="Masuk">
    <div class="flex min-h-screen flex-col px-6 py-8">
        <p class="karsa-wordmark text-2xl text-forest-800">KARSA</p>
        <h1 class="mt-6 font-display text-2xl font-bold text-forest-950">Masuk ke akun Anda</h1>
        <p class="mt-1 text-sm text-ink-500">Pantau risiko dan kirim laporan karhutla di wilayah Anda.</p>

        @if (session('status'))
            <div class="mt-4 rounded-2xl bg-leaf-100 p-4 text-sm text-forest-950">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mt-4 rounded-2xl bg-risk-extreme/10 p-4 text-sm text-risk-extreme">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="mt-6 flex flex-1 flex-col gap-4">
            @csrf

            <div>
                <label for="login" class="mb-1.5 block text-sm font-medium text-forest-950">Email atau nomor HP</label>
                <input id="login" name="login" type="text" required autofocus value="{{ old('login') }}"
                       class="w-full rounded-2xl border border-leaf-100 px-4 py-3 text-sm focus:border-forest-600 focus:outline-none">
            </div>

            <div x-data="{ show: false }">
                <div class="flex items-center justify-between">
                    <label for="password" class="mb-1.5 block text-sm font-medium text-forest-950">Kata sandi</label>
                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-forest-800">Lupa kata sandi?</a>
                </div>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" id="password" name="password" required
                           class="w-full rounded-2xl border border-leaf-100 px-4 py-3 pr-11 text-sm focus:border-forest-600 focus:outline-none">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-ink-500">
                        <x-icon name="eye" class="h-5 w-5" x-show="!show" />
                        <x-icon name="eye-off" class="h-5 w-5" x-show="show" x-cloak />
                    </button>
                </div>
            </div>

            <label class="flex items-center gap-2.5 text-sm text-ink-500">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-leaf-100 text-forest-800">
                Ingat saya
            </label>

            <div class="mt-auto space-y-3 pt-4">
                <x-primary-button type="submit">Masuk</x-primary-button>

                <a href="{{ route('auth.google') }}"
                   class="flex h-14 w-full items-center justify-center gap-2 rounded-2xl border border-leaf-100 text-sm font-semibold text-forest-950">
                    Masuk dengan Google
                </a>

                <p class="text-center text-sm text-ink-500">
                    Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-forest-800">Daftar</a>
                </p>
            </div>
        </form>
    </div>
</x-layouts.public>
