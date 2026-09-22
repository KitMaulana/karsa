<?php

namespace App\Livewire\Admin\RiskModel;

use App\Models\ActivityLog;
use App\Models\RiskModel as RiskModelModel;
use App\Services\Risk\AhpCalculator;
use App\Services\Risk\RiskScorer;
use Livewire\Component;

class Index extends Component
{
    public array $mainMatrix = [
        [1, 2, 3],
        [0.5, 1, 2],
        [0.333, 0.5, 1],
    ];

    public array $subMatrix = [
        [1, 2, 3, 2],
        [0.5, 1, 2, 1],
        [0.333, 0.5, 1, 0.5],
        [0.5, 1, 2, 1],
    ];

    public array $mainLabels = ['Hotspot', 'Cuaca', 'Kerentanan'];

    public array $subLabels = ['Suhu maks', 'Kelembapan min', 'Kecepatan angin', 'Hari tanpa hujan'];

    // Simulator
    public float $simHotspot = 3;

    public float $simTempMax = 32;

    public float $simRhMin = 60;

    public float $simWindMax = 15;

    public float $simDryDays = 7;

    public float $simVulnerability = 30;

    public string $modelName = '';

    public function mount(): void
    {
        $active = RiskModelModel::active();

        if ($active) {
            $this->mainMatrix = $active->matrix;
            $this->subMatrix = $active->sub_matrix;
        }
    }

    public function setMainCell(int $i, int $j, float $value): void
    {
        $this->mainMatrix[$i][$j] = $value;
        $this->mainMatrix[$j][$i] = $value != 0 ? round(1 / $value, 4) : 1;
    }

    public function setSubCell(int $i, int $j, float $value): void
    {
        $this->subMatrix[$i][$j] = $value;
        $this->subMatrix[$j][$i] = $value != 0 ? round(1 / $value, 4) : 1;
    }

    public function getMainResultProperty()
    {
        return (new AhpCalculator)->calculate($this->mainMatrix);
    }

    public function getSubResultProperty()
    {
        return (new AhpCalculator)->calculate($this->subMatrix);
    }

    public function getSimulationScoreProperty(): array
    {
        $scorer = new RiskScorer;
        $main = $this->mainResult;
        $sub = $this->subResult;

        $sHotspot = $scorer->normalizeHotspot($this->simHotspot);
        $sWeather = $scorer->scoreWeather(
            ['temp_max' => $this->simTempMax, 'rh_min' => $this->simRhMin, 'wind_max' => $this->simWindMax, 'dry_days' => $this->simDryDays],
            ['temp_max' => $sub->weights[0], 'rh_min' => $sub->weights[1], 'wind_max' => $sub->weights[2], 'dry_days' => $sub->weights[3]],
            config('karsa.risk.weather_bounds')
        );

        $score = $scorer->finalScore($sHotspot, $sWeather, $this->simVulnerability, [
            'hotspot' => $main->weights[0], 'cuaca' => $main->weights[1], 'kerentanan' => $main->weights[2],
        ]);

        return ['s_hotspot' => $sHotspot, 's_weather' => $sWeather, 'score' => $score, 'level' => \App\Enums\RiskLevel::fromScore($score)];
    }

    public function save(): void
    {
        $main = $this->mainResult;
        $sub = $this->subResult;

        if (! $main->isConsistent() || ! $sub->isConsistent()) {
            $this->addError('cr', 'CR harus ≤ 0,10 sebelum model bisa disimpan. Sesuaikan nilai perbandingan.');

            return;
        }

        RiskModelModel::query()->update(['is_active' => false]);

        $model = RiskModelModel::create([
            'name' => $this->modelName ?: 'Model AHP '.now()->format('d M Y H:i'),
            'matrix' => $this->mainMatrix,
            'sub_matrix' => $this->subMatrix,
            'weights' => ['hotspot' => round($main->weights[0], 4), 'cuaca' => round($main->weights[1], 4), 'kerentanan' => round($main->weights[2], 4)],
            'sub_weights' => ['temp_max' => round($sub->weights[0], 4), 'rh_min' => round($sub->weights[1], 4), 'wind_max' => round($sub->weights[2], 4), 'dry_days' => round($sub->weights[3], 4)],
            'cr' => round($main->cr, 4),
            'sub_cr' => round($sub->cr, 4),
            'thresholds' => config('karsa.risk.thresholds'),
            'is_active' => true,
        ]);

        ActivityLog::record('simpan_model_ahp', $model, ['cr' => $model->cr]);
        session()->flash('status', 'Model AHP baru disimpan dan diaktifkan.');
    }

    public function activate(int $id): void
    {
        RiskModelModel::query()->update(['is_active' => false]);
        $model = RiskModelModel::findOrFail($id);
        $model->update(['is_active' => true]);
        ActivityLog::record('aktifkan_model_ahp', $model);
        session()->flash('status', "Model \"{$model->name}\" diaktifkan.");
    }

    public function render()
    {
        return view('livewire.admin.risk-model.index', [
            'history' => RiskModelModel::latest()->limit(10)->get(),
        ])->layout('components.layouts.admin', ['title' => 'Model Risiko (AHP)']);
    }
}
