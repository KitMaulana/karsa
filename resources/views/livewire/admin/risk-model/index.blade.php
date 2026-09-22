@php
    $saaty = [1/9 => '1/9', 1/8 => '1/8', 1/7 => '1/7', 1/6 => '1/6', 1/5 => '1/5', 1/4 => '1/4', 1/3 => '1/3', 1/2 => '1/2', 1 => '1 (setara)', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8', 9 => '9'];
@endphp

<div class="space-y-6">
    @if(session('status'))
        <div class="karsa-card bg-leaf-100/40 p-3 text-sm text-forest-950">{{ session('status') }}</div>
    @endif
    @error('cr')
        <div class="karsa-card bg-risk-extreme/10 p-3 text-sm text-risk-extreme">{{ $message }}</div>
    @enderror

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Matriks utama --}}
        <div class="karsa-card p-4">
            <h2 class="font-display font-bold text-forest-950">Matriks perbandingan utama</h2>
            <table class="mt-3 w-full text-sm">
                <thead><tr><th></th>@foreach($mainLabels as $l)<th class="px-2 py-1 text-xs text-ink-500">{{ $l }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach($mainMatrix as $i => $row)
                        <tr>
                            <th class="px-2 py-1 text-left text-xs text-ink-500">{{ $mainLabels[$i] }}</th>
                            @foreach($row as $j => $val)
                                <td class="px-1 py-1">
                                    @if($i === $j)
                                        <div class="rounded-lg bg-leaf-100 py-1.5 text-center text-xs">1</div>
                                    @elseif($i < $j)
                                        <select wire:change="setMainCell({{ $i }}, {{ $j }}, $event.target.value)" class="w-full rounded-lg border border-leaf-100 py-1 text-xs">
                                            @foreach($saaty as $val2 => $label)
                                                <option value="{{ $val2 }}" @selected(abs($val - $val2) < 0.001)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="rounded-lg bg-cream-50 py-1.5 text-center text-xs text-ink-500">{{ round($val, 3) }}</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 space-y-1.5 text-sm">
                @foreach($mainLabels as $i => $label)
                    <div class="flex justify-between"><span>{{ $label }}</span><strong>{{ number_format($this->mainResult->weights[$i] * 100, 1) }}%</strong></div>
                @endforeach
                <div class="flex justify-between border-t border-leaf-100 pt-1.5">
                    <span>Consistency Ratio (CR)</span>
                    <strong class="{{ $this->mainResult->isConsistent() ? 'text-forest-800' : 'text-risk-extreme' }}">{{ number_format($this->mainResult->cr, 4) }}</strong>
                </div>
            </div>
        </div>

        {{-- Matriks sub-faktor cuaca --}}
        <div class="karsa-card p-4">
            <h2 class="font-display font-bold text-forest-950">Matriks sub-faktor cuaca</h2>
            <table class="mt-3 w-full text-sm">
                <thead><tr><th></th>@foreach($subLabels as $l)<th class="px-2 py-1 text-xs text-ink-500">{{ $l }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach($subMatrix as $i => $row)
                        <tr>
                            <th class="px-2 py-1 text-left text-xs text-ink-500">{{ $subLabels[$i] }}</th>
                            @foreach($row as $j => $val)
                                <td class="px-1 py-1">
                                    @if($i === $j)
                                        <div class="rounded-lg bg-leaf-100 py-1.5 text-center text-xs">1</div>
                                    @elseif($i < $j)
                                        <select wire:change="setSubCell({{ $i }}, {{ $j }}, $event.target.value)" class="w-full rounded-lg border border-leaf-100 py-1 text-xs">
                                            @foreach($saaty as $val2 => $label)
                                                <option value="{{ $val2 }}" @selected(abs($val - $val2) < 0.001)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div class="rounded-lg bg-cream-50 py-1.5 text-center text-xs text-ink-500">{{ round($val, 3) }}</div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 space-y-1.5 text-sm">
                @foreach($subLabels as $i => $label)
                    <div class="flex justify-between"><span>{{ $label }}</span><strong>{{ number_format($this->subResult->weights[$i] * 100, 1) }}%</strong></div>
                @endforeach
                <div class="flex justify-between border-t border-leaf-100 pt-1.5">
                    <span>Consistency Ratio (CR)</span>
                    <strong class="{{ $this->subResult->isConsistent() ? 'text-forest-800' : 'text-risk-extreme' }}">{{ number_format($this->subResult->cr, 4) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="karsa-card p-4">
        <div class="flex flex-wrap items-center gap-3">
            <input type="text" wire:model="modelName" placeholder="Nama model (opsional)" class="rounded-xl border border-leaf-100 px-3 py-2 text-sm">
            <button wire:click="save" class="karsa-btn-primary !h-11 !w-auto px-6">Simpan & aktifkan model ini</button>
        </div>
    </div>

    {{-- Simulator --}}
    <div class="karsa-card p-4">
        <h2 class="font-display font-bold text-forest-950">Simulator skor</h2>
        <div class="mt-3 grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-xs text-ink-500">Jumlah hotspot berbobot ({{ $simHotspot }})</label>
                <input type="range" min="0" max="15" step="0.5" wire:model.live="simHotspot" class="w-full">
            </div>
            <div>
                <label class="text-xs text-ink-500">Kerentanan wilayah ({{ $simVulnerability }})</label>
                <input type="range" min="0" max="100" wire:model.live="simVulnerability" class="w-full">
            </div>
            <div>
                <label class="text-xs text-ink-500">Suhu maks °C ({{ $simTempMax }})</label>
                <input type="range" min="20" max="42" wire:model.live="simTempMax" class="w-full">
            </div>
            <div>
                <label class="text-xs text-ink-500">Kelembapan min % ({{ $simRhMin }})</label>
                <input type="range" min="20" max="95" wire:model.live="simRhMin" class="w-full">
            </div>
            <div>
                <label class="text-xs text-ink-500">Kecepatan angin km/jam ({{ $simWindMax }})</label>
                <input type="range" min="0" max="50" wire:model.live="simWindMax" class="w-full">
            </div>
            <div>
                <label class="text-xs text-ink-500">Hari tanpa hujan ({{ $simDryDays }})</label>
                <input type="range" min="0" max="25" wire:model.live="simDryDays" class="w-full">
            </div>
        </div>

        <div class="mt-4 flex items-center gap-4 rounded-2xl bg-leaf-100/40 p-4">
            <span class="font-display text-3xl font-extrabold text-forest-950">{{ number_format($this->simulationScore['score'], 1) }}</span>
            <x-risk-pill :level="$this->simulationScore['level']" />
        </div>
    </div>

    {{-- Riwayat versi --}}
    <div class="karsa-card overflow-x-auto">
        <h2 class="p-4 pb-0 font-display font-bold text-forest-950">Riwayat versi model</h2>
        <table class="mt-3 w-full text-sm">
            <thead class="border-y border-leaf-100 bg-leaf-100/30 text-left text-xs text-ink-500">
                <tr><th class="px-4 py-2">Nama</th><th class="px-4 py-2">Bobot</th><th class="px-4 py-2">CR</th><th class="px-4 py-2">Dibuat</th><th class="px-4 py-2">Status</th><th class="px-4 py-2"></th></tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @foreach($history as $h)
                    <tr>
                        <td class="px-4 py-2.5 font-medium text-forest-950">{{ $h->name }}</td>
                        <td class="px-4 py-2.5 text-xs">H {{ number_format($h->weights['hotspot']*100,0) }}% · C {{ number_format($h->weights['cuaca']*100,0) }}% · K {{ number_format($h->weights['kerentanan']*100,0) }}%</td>
                        <td class="px-4 py-2.5">{{ number_format($h->cr, 4) }}</td>
                        <td class="px-4 py-2.5 text-ink-500">{{ $h->created_at->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-2.5">{{ $h->is_active ? 'Aktif' : '–' }}</td>
                        <td class="px-4 py-2.5">
                            @unless($h->is_active)
                                <button wire:click="activate({{ $h->id }})" class="font-medium text-forest-800">Aktifkan</button>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
