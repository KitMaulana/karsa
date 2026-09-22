<?php

namespace App\Livewire\Admin\Alerts;

use App\Models\Alert;
use App\Models\District;
use App\Services\Notify\EmailAuthorityNotifier;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $districtId = null;

    public string $level = 'tinggi';

    public string $message = '';

    public bool $showForm = false;

    public function broadcast(): void
    {
        $this->validate([
            'districtId' => 'required|exists:districts,id',
            'level' => 'required|in:rendah,sedang,tinggi,sangat_tinggi',
            'message' => 'required|string|min:10',
        ]);

        $alert = Alert::create([
            'district_id' => $this->districtId,
            'to_level' => $this->level,
            'score' => 0,
            'is_manual' => true,
            'message' => $this->message,
            'created_by' => auth()->id(),
            'sent_at' => now(),
        ]);

        app(\App\Services\Notify\AlertDispatcher::class)->dispatch($alert);

        \App\Models\ActivityLog::record('peringatan_manual', null, ['district_id' => $this->districtId]);

        $this->reset(['message', 'showForm']);
        session()->flash('status', 'Peringatan manual berhasil dikirim.');
    }

    public function whatsappLink(Alert $alert): ?string
    {
        $contact = \App\Models\AuthorityContact::where('district_id', $alert->district_id)->first();

        return $contact ? EmailAuthorityNotifier::whatsappLink($contact, $alert->message) : null;
    }

    public function render()
    {
        return view('livewire.admin.alerts.index', [
            'alerts' => Alert::with('district')->latest()->paginate(20),
            'districts' => District::orderBy('name')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Peringatan']);
    }
}
