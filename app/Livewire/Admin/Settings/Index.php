<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Livewire\Component;

class Index extends Component
{
    public string $sipongiUrl = '';

    public string $firmsMapKey = '';

    public array $providerPriority = ['sipongi', 'firms'];

    public float $bufferKm = 5;

    public float $luasPerHotspotHa = 1;

    public bool $sipongiEmbedEnabled = false;

    public bool $donasiEnabled = false;

    public function mount(): void
    {
        $this->sipongiUrl = (string) config('karsa.hotspot.sipongi.url');
        $this->firmsMapKey = config('karsa.hotspot.firms.map_key') ? str_repeat('•', 8) : '';
        $this->providerPriority = Setting::get('provider_priority', config('karsa.hotspot.provider_priority'));
        $this->bufferKm = (float) Setting::get('buffer_km', config('karsa.risk.buffer_km'));
        $this->luasPerHotspotHa = (float) Setting::get('luas_per_hotspot_ha', config('karsa.co2.luas_per_hotspot_ha'));
        $this->sipongiEmbedEnabled = (bool) Setting::get('sipongi_embed_enabled', config('karsa.sipongi_embed_enabled'));
        $this->donasiEnabled = (bool) Setting::get('donasi_enabled', config('karsa.donasi_enabled'));
    }

    public function save(): void
    {
        Setting::set('provider_priority', $this->providerPriority);
        Setting::set('buffer_km', $this->bufferKm);
        Setting::set('luas_per_hotspot_ha', $this->luasPerHotspotHa);
        Setting::set('sipongi_embed_enabled', $this->sipongiEmbedEnabled);
        Setting::set('donasi_enabled', $this->donasiEnabled);

        \App\Models\ActivityLog::record('ubah_pengaturan');
        session()->flash('status', 'Pengaturan disimpan. Kunci API (SIPONGI_HOTSPOT_URL, FIRMS_MAP_KEY, VAPID) diatur langsung di file .env server.');
    }

    public function render()
    {
        return view('livewire.admin.settings.index')
            ->layout('components.layouts.admin', ['title' => 'Pengaturan']);
    }
}
