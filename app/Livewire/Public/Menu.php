<?php

namespace App\Livewire\Public;

use App\Models\District;
use App\Models\RiskScore;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Menu extends Component
{
    public ?RiskScore $myRiskScore = null;

    public ?District $myDistrict = null;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $this->myDistrict = $user->watchDistricts()->first();

        if ($this->myDistrict) {
            $this->myRiskScore = $this->myDistrict->riskScores()->latest('calculated_at')->first();
        }
    }

    public function render()
    {
        $items = [
            ['route' => 'peta', 'icon' => 'map', 'title' => 'Peta Risiko Karhutla', 'subtitle' => 'Lihat sebaran risiko & hotspot terkini'],
            ['route' => 'aksi', 'icon' => 'leaf', 'title' => 'Aksi Pencegahan', 'subtitle' => 'Rekomendasi langkah 72 jam ke depan'],
            ['route' => 'lapor', 'icon' => 'camera', 'title' => 'Pelaporan', 'subtitle' => 'Laporkan titik asap atau api'],
            ['route' => 'peringatan', 'icon' => 'megaphone', 'title' => 'Peringatan & Status', 'subtitle' => 'Riwayat & peringatan aktif'],
            ['route' => 'news.index', 'icon' => 'newspaper', 'title' => 'KARSA News', 'subtitle' => 'Berita & edukasi karhutla'],
            ['route' => 'program.index', 'icon' => 'gift', 'title' => 'Penggalangan Dana', 'subtitle' => 'Program aksi & donasi'],
        ];

        return view('livewire.public.menu', ['items' => $items])
            ->layout('components.layouts.public', ['title' => 'Menu', 'withBottomNav' => true]);
    }
}
