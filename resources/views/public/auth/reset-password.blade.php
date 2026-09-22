<x-layouts.public title="Atur Ulang Kata Sandi">
    <div class="flex min-h-screen flex-col px-6 py-8">
        <p class="karsa-wordmark text-2xl text-forest-800">KARSA</p>
        <h1 class="mt-6 font-display text-2xl font-bold text-forest-950">Atur ulang kata sandi</h1>

        @if ($errors->any())
            <div class="mt-4 rounded-2xl bg-risk-extreme/10 p-4 text-sm text-risk-extreme">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 flex flex-1 flex-col gap-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-forest-950">Email</label>
                <input id="email" name="email" type="email" required value="{{ $email }}"
                       class="w-full rounded-2xl border border-leaf-100 px-4 py-3 text-sm focus:border-forest-600 focus:outline-none">
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-forest-950">Kata sandi baru</label>
                <input id="password" name="password" type="password" required minlength="8"
                       class="w-full rounded-2xl border border-leaf-100 px-4 py-3 text-sm focus:border-forest-600 focus:outline-none">
            </div>

            <div>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8"
                       placeholder="Ulangi kata sandi baru"
                       class="w-full rounded-2xl border border-leaf-100 px-4 py-3 text-sm focus:border-forest-600 focus:outline-none">
            </div>

            <div class="mt-auto pt-4">
                <x-primary-button type="submit">Simpan kata sandi baru</x-primary-button>
            </div>
        </form>
    </div>
</x-layouts.public>
