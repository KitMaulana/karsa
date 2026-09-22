<?php

namespace App\Livewire\Admin\Regions;

use App\Models\ActivityLog;
use App\Models\District;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public $geojsonFile = null;

    public string $importLevel = 'district';

    public ?int $editingId = null;

    public int $vulnerabilityScore = 0;

    public array $vulnerabilityFactors = [
        'tutupan_lahan' => 0,
        'lahan_gambut' => 0,
        'riwayat_kebakaran' => 0,
        'jarak_permukiman' => 0,
        'akses_pemadam' => 0,
    ];

    public bool $isMonitored = true;

    public string $importMessage = '';

    public function edit(int $districtId): void
    {
        $district = District::findOrFail($districtId);
        $this->editingId = $districtId;
        $this->vulnerabilityScore = $district->vulnerability_score;
        $this->vulnerabilityFactors = $district->vulnerability_factors ?? $this->vulnerabilityFactors;
        $this->isMonitored = $district->is_monitored;
    }

    public function save(): void
    {
        $district = District::findOrFail($this->editingId);

        $avg = (int) round(array_sum($this->vulnerabilityFactors) / max(1, count($this->vulnerabilityFactors)));

        $district->update([
            'vulnerability_score' => $avg,
            'vulnerability_factors' => $this->vulnerabilityFactors,
            'is_monitored' => $this->isMonitored,
        ]);

        ActivityLog::record('ubah_kerentanan_wilayah', $district, ['vulnerability_score' => $avg]);

        $this->editingId = null;
        session()->flash('status', 'Data wilayah diperbarui.');
    }

    public function importGeojson(): void
    {
        $this->validate(['geojsonFile' => 'required|file|mimes:json,geojson']);

        $path = $this->geojsonFile->getRealPath();

        \Illuminate\Support\Facades\Artisan::call('karsa:import-geojson', [
            'file' => $path,
            '--level' => $this->importLevel,
        ]);

        $this->importMessage = trim(\Illuminate\Support\Facades\Artisan::output());
        $this->geojsonFile = null;
    }

    public function render()
    {
        return view('livewire.admin.regions.index', [
            'districts' => District::with('regency')->orderBy('name')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Wilayah']);
    }
}
