<?php

namespace App\Livewire\Admin\Hotspots;

use App\Models\DataSyncLog;
use App\Models\District;
use App\Models\Hotspot;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $source = '';

    public string $confidence = '';

    public ?int $districtId = null;

    public string $syncMessage = '';

    public function sync(): void
    {
        Artisan::call('karsa:sync-hotspots');
        $this->syncMessage = trim(Artisan::output()) ?: 'Sinkronisasi selesai.';
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $hotspots = Hotspot::with('district')->latest('detected_at')->limit(5000)->get();

        return response()->streamDownload(function () use ($hotspots) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'sumber', 'satelit', 'lat', 'lng', 'confidence', 'frp', 'terdeteksi', 'kecamatan', 'terkonfirmasi']);
            foreach ($hotspots as $h) {
                fputcsv($out, [$h->id, implode('|', $h->sources ?? []), $h->satellite, $h->lat, $h->lng, $h->confidence->value, $h->frp, $h->detected_at, $h->district?->name, $h->corroborated ? 'ya' : 'tidak']);
            }
            fclose($out);
        }, 'hotspots.csv');
    }

    public function exportGeojson(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $hotspots = Hotspot::latest('detected_at')->limit(5000)->get();

        $geojson = [
            'type' => 'FeatureCollection',
            'features' => $hotspots->map(fn (Hotspot $h) => [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [$h->lng, $h->lat]],
                'properties' => ['confidence' => $h->confidence->value, 'detected_at' => $h->detected_at->toIso8601String(), 'sources' => $h->sources],
            ]),
        ];

        return response()->streamDownload(function () use ($geojson) {
            echo json_encode($geojson);
        }, 'hotspots.geojson');
    }

    public function render()
    {
        $query = Hotspot::with('district')->latest('detected_at');

        if ($this->source) {
            $query->whereJsonContains('sources', $this->source);
        }
        if ($this->confidence) {
            $query->where('confidence', $this->confidence);
        }
        if ($this->districtId) {
            $query->where('district_id', $this->districtId);
        }

        return view('livewire.admin.hotspots.index', [
            'hotspots' => $query->paginate(25),
            'districts' => District::orderBy('name')->get(),
            'logs' => DataSyncLog::whereIn('source', ['sipongi', 'firms'])->latest()->limit(10)->get(),
        ])->layout('components.layouts.admin', ['title' => 'Hotspot']);
    }
}
