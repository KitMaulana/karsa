<div>
    <x-forest-header title="Profil" :back="true" />

    <div class="space-y-5 px-5 py-6">
        @if(session('status'))
            <x-card class="bg-leaf-100/60 text-sm text-forest-950">{{ session('status') }}</x-card>
        @endif

        <x-card class="flex items-center gap-4">
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-leaf-100">
                <x-icon name="user" class="h-6 w-6 text-forest-800" />
            </span>
            <div>
                <p class="font-display font-bold text-forest-950">{{ auth()->user()->name }}</p>
                <p class="text-sm text-ink-500">{{ auth()->user()->email ?? auth()->user()->phone }}</p>
            </div>
        </x-card>

        <x-card>
            <h2 class="font-display font-bold text-forest-950">Nilai kepercayaan</h2>
            <div class="mt-2 flex items-center gap-3">
                <div class="h-2 flex-1 rounded-full bg-leaf-100">
                    <div class="h-2 rounded-full bg-forest-600" style="width: {{ auth()->user()->trust_score }}%"></div>
                </div>
                <span class="font-semibold text-forest-950">{{ auth()->user()->trust_score }}/100</span>
            </div>
        </x-card>

        <x-card>
            <h2 class="font-display font-bold text-forest-950">Kabupaten/kota</h2>
            <select wire:model="homeRegencyId" wire:change="updateRegency" class="mt-2 w-full rounded-2xl border border-leaf-100 px-4 py-3 text-sm">
                @foreach($regencies as $regency)
                    <option value="{{ $regency->id }}">{{ $regency->name }}</option>
                @endforeach
            </select>
        </x-card>

        <x-card>
            <h2 class="font-display font-bold text-forest-950">Wilayah pantauan</h2>
            <p class="mt-1 text-xs text-ink-500">Pilih maksimal 3 kecamatan untuk menerima peringatan dini.</p>
            <div class="mt-3 max-h-64 space-y-1 overflow-y-auto">
                @foreach($districts as $district)
                    <label class="flex items-center gap-2.5 rounded-xl px-2 py-2 text-sm hover:bg-leaf-100/40">
                        <input type="checkbox" wire:click="toggleWatch({{ $district->id }})" @checked(in_array($district->id, $watchDistrictIds))
                               class="h-4 w-4 rounded border-leaf-100 text-forest-800">
                        {{ $district->name }}
                    </label>
                @endforeach
            </div>
        </x-card>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex h-14 w-full items-center justify-center gap-2 rounded-2xl border border-risk-extreme/30 text-sm font-semibold text-risk-extreme">
                <x-icon name="logout" class="h-5 w-5" /> Keluar
            </button>
        </form>
    </div>
</div>
