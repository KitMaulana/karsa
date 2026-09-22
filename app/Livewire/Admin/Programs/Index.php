<?php

namespace App\Livewire\Admin\Programs;

use App\Models\Program;
use Illuminate\Support\Str;
use Livewire\Component;

class Index extends Component
{
    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public ?string $startsAt = null;

    public string $location = '';

    public ?int $volunteerQuota = null;

    public bool $donationEnabled = false;

    public ?float $donationTarget = null;

    public float $donationCollected = 0;

    public string $usageReport = '';

    public string $status = 'draf';

    public function edit(?int $id = null): void
    {
        $this->reset(['editingId', 'title', 'description', 'startsAt', 'location', 'volunteerQuota', 'donationEnabled', 'donationTarget', 'donationCollected', 'usageReport', 'status']);
        $this->editingId = $id ?? 0;

        if ($id) {
            $p = Program::findOrFail($id);
            $this->title = $p->title;
            $this->description = $p->description;
            $this->startsAt = $p->starts_at?->format('Y-m-d\TH:i');
            $this->location = (string) $p->location;
            $this->volunteerQuota = $p->volunteer_quota;
            $this->donationEnabled = $p->donation_enabled;
            $this->donationTarget = $p->donation_target;
            $this->donationCollected = (float) $p->donation_collected;
            $this->usageReport = (string) $p->usage_report;
            $this->status = $p->status;
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string',
            'status' => 'required|in:draf,terbit,selesai',
        ]);

        $existing = $this->editingId ? Program::find($this->editingId) : null;

        Program::updateOrCreate(
            ['id' => $this->editingId ?: null],
            [
                'title' => $this->title,
                'slug' => $existing?->slug ?? Str::slug($this->title).'-'.Str::random(4),
                'description' => $this->description,
                'starts_at' => $this->startsAt,
                'location' => $this->location,
                'volunteer_quota' => $this->volunteerQuota,
                'donation_enabled' => $this->donationEnabled,
                'donation_target' => $this->donationTarget,
                'donation_collected' => $this->donationCollected,
                'usage_report' => $this->usageReport,
                'status' => $this->status,
            ]
        );

        $this->editingId = null;
        session()->flash('status', 'Program disimpan.');
    }

    public function render()
    {
        return view('livewire.admin.programs.index', [
            'programs' => Program::withCount('participants')->latest()->get(),
            'donasiEnabled' => (bool) \App\Models\Setting::get('donasi_enabled', config('karsa.donasi_enabled')),
        ])->layout('components.layouts.admin', ['title' => 'Program & Donasi']);
    }

    public function toggleDonasi(): void
    {
        $current = (bool) \App\Models\Setting::get('donasi_enabled', config('karsa.donasi_enabled'));
        \App\Models\Setting::set('donasi_enabled', ! $current);
    }
}
