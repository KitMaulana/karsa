<?php

namespace App\Livewire\Public;

use App\Models\District;
use App\Models\Regency;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Profil extends Component
{
    public ?int $homeRegencyId = null;

    public array $watchDistrictIds = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->homeRegencyId = $user->home_regency_id;
        $this->watchDistrictIds = $user->watchDistricts()->pluck('districts.id')->all();
    }

    public function updateRegency(): void
    {
        $this->validate(['homeRegencyId' => 'required|exists:regencies,id']);

        Auth::user()->update(['home_regency_id' => $this->homeRegencyId]);
        session()->flash('status', 'Kabupaten/kota berhasil disimpan.');
    }

    public function toggleWatch(int $districtId): void
    {
        if (in_array($districtId, $this->watchDistrictIds, true)) {
            $this->watchDistrictIds = array_values(array_diff($this->watchDistrictIds, [$districtId]));
        } elseif (count($this->watchDistrictIds) < 3) {
            $this->watchDistrictIds[] = $districtId;
        } else {
            session()->flash('status', 'Maksimal 3 kecamatan pantauan.');

            return;
        }

        Auth::user()->watchDistricts()->sync($this->watchDistrictIds);
        session()->flash('status', 'Wilayah pantauan diperbarui.');
    }

    public function render()
    {
        return view('livewire.public.profil', [
            'regencies' => Regency::orderBy('name')->get(),
            'districts' => District::where('is_monitored', true)->orderBy('name')->get(),
        ])->layout('components.layouts.public', ['title' => 'Profil', 'withBottomNav' => true]);
    }
}
