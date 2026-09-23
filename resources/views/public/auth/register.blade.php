<x-layouts.public title="Daftar">
    <div class="flex min-h-screen flex-col px-6 py-8">
        <p class="karsa-wordmark text-2xl text-forest-800">KARSA</p>
        <h1 class="mt-6 font-display text-2xl font-bold text-forest-950">Buat akun baru</h1>
        <p class="mt-1 text-sm text-ink-500">Pantau risiko karhutla dan ikut menjaga hutan di wilayah Anda.</p>

        @if ($errors->any())
            <div class="mt-4 rounded-2xl bg-risk-extreme/10 p-4 text-sm text-risk-extreme">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 flex flex-1 flex-col gap-4">
            @csrf

            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-forest-950">Nama lengkap</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}"
                       class="w-full rounded-2xl border border-leaf-100 px-4 py-3 text-sm focus:border-forest-600 focus:outline-none">
            </div>

            <div>
                <label for="identity" class="mb-1.5 block text-sm font-medium text-forest-950">Email atau nomor HP</label>
                <input id="identity" name="identity" type="text" required value="{{ old('identity') }}"
                       placeholder="nama@email.com atau 08xxxxxxxxxx"
                       class="w-full rounded-2xl border border-leaf-100 px-4 py-3 text-sm focus:border-forest-600 focus:outline-none">
            </div>

            <div>
                <label for="regency_id" class="mb-1.5 block text-sm font-medium text-forest-950">Kabupaten/kota</label>
                <select id="regency_id" name="regency_id" required
                        class="w-full rounded-2xl border border-leaf-100 px-4 py-3 text-sm focus:border-forest-600 focus:outline-none">
                    <option value="">Pilih kabupaten/kota</option>
                    @foreach($regencies as $regency)
                        <option value="{{ $regency->id }}" @selected(old('regency_id') == $regency->id)>{{ $regency->name }}</option>
                    @endforeach
                </select>
            </div>

            <div x-data="{ show: false }">
                <label for="password" class="mb-1.5 block text-sm font-medium text-forest-950">Kata sandi</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" id="password" name="password" required minlength="8"
                           class="w-full rounded-2xl border border-leaf-100 px-4 py-3 pr-11 text-sm focus:border-forest-600 focus:outline-none">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-3 flex items-center text-ink-500" :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                        <x-icon name="eye" class="h-5 w-5" x-show="!show" />
                        <x-icon name="eye-off" class="h-5 w-5" x-show="show" x-cloak />
                    </button>
                </div>
                <p class="mt-1 text-xs text-ink-500">Minimal 8 karakter.</p>
            </div>

            <div>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                       placeholder="Ulangi kata sandi"
                       class="w-full rounded-2xl border border-leaf-100 px-4 py-3 text-sm focus:border-forest-600 focus:outline-none">
            </div>

            <label class="flex items-start gap-2.5 text-sm text-ink-500">
                <input type="checkbox" name="terms" value="1" required class="mt-0.5 h-4 w-4 rounded border-leaf-100 text-forest-800">
                <span>Saya menyetujui <a href="{{ route('syarat') }}" class="font-medium text-forest-800 underline">Syarat</a>
                    &amp; <a href="{{ route('privasi') }}" class="font-medium text-forest-800 underline">Kebijakan Privasi</a>.</span>
            </label>

            <div class="mt-auto space-y-3 pt-4">
                <x-primary-button type="submit">Daftar</x-primary-button>

                <p class="text-center text-sm text-ink-500">
                    Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-forest-800">Masuk</a>
                </p>
            </div>
        </form>
    </div>
</x-layouts.public>
