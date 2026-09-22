<x-layouts.public title="Lupa Kata Sandi">
    <div class="flex min-h-screen flex-col px-6 py-8">
        <p class="karsa-wordmark text-2xl text-forest-800">KARSA</p>
        <h1 class="mt-6 font-display text-2xl font-bold text-forest-950">Lupa kata sandi?</h1>
        <p class="mt-1 text-sm text-ink-500">Masukkan email akun Anda, kami akan mengirim tautan atur ulang kata sandi.</p>

        @if (session('status'))
            <div class="mt-4 rounded-2xl bg-leaf-100 p-4 text-sm text-forest-950">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mt-4 rounded-2xl bg-risk-extreme/10 p-4 text-sm text-risk-extreme">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 flex flex-1 flex-col gap-4">
            @csrf
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-forest-950">Email</label>
                <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
                       class="w-full rounded-2xl border border-leaf-100 px-4 py-3 text-sm focus:border-forest-600 focus:outline-none">
            </div>

            <div class="mt-auto pt-4">
                <x-primary-button type="submit">Kirim tautan atur ulang</x-primary-button>
                <p class="mt-3 text-center text-sm text-ink-500">
                    <a href="{{ route('login') }}" class="font-semibold text-forest-800">Kembali ke halaman masuk</a>
                </p>
            </div>
        </form>
    </div>
</x-layouts.public>
