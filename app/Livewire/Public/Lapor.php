<?php

namespace App\Livewire\Public;

use App\Services\Reports\ReportSubmissionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Lapor extends Component
{
    use WithFileUploads;

    #[Validate('required|numeric|between:-90,90')]
    public ?float $lat = null;

    #[Validate('required|numeric|between:-180,180')]
    public ?float $lng = null;

    public ?float $accuracyM = null;

    #[Validate('required|in:asap,api_kecil,api_besar,pembakaran_lahan')]
    public string $type = '';

    #[Validate('required|string|min:10|max:1000')]
    public string $description = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    #[Validate('required|array|min:1|max:3')]
    public array $media = [];

    // Honeypot -- kolom tersembunyi, harus selalu kosong (CLAUDE.md §11.2).
    public string $website = '';

    public bool $submitted = false;

    public bool $offline = false;

    public function useMyLocation(): void
    {
        $this->dispatch('request-geolocation');
    }

    public function setLocation(float $lat, float $lng, ?float $accuracy = null): void
    {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->accuracyM = $accuracy;
    }

    public function submit(ReportSubmissionService $service): void
    {
        if (filled($this->website)) {
            // Honeypot terisi -- diam-diam anggap sukses tanpa memproses (anti-bot).
            $this->submitted = true;

            return;
        }

        $this->validate();

        $userReportsThisHour = \App\Models\Report::where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($userReportsThisHour >= config('karsa.reports.rate_limit_per_hour_user', 3)) {
            $this->addError('description', 'Anda sudah mengirim terlalu banyak laporan dalam satu jam terakhir. Coba lagi nanti.');

            return;
        }

        $service->submit(
            Auth::user(),
            [
                'lat' => $this->lat,
                'lng' => $this->lng,
                'accuracy_m' => $this->accuracyM,
                'type' => $this->type,
                'description' => $this->description,
            ],
            $this->media
        );

        $this->submitted = true;
        $this->reset(['lat', 'lng', 'accuracyM', 'type', 'description', 'media']);
    }

    public function render()
    {
        return view('livewire.public.lapor')
            ->layout('components.layouts.public', ['title' => 'Pelaporan Karhutla']);
    }
}
