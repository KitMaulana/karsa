<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $title = 'Penanaman Pohon Bersama Wapala SMAN 1 Ciruas';

        Program::updateOrCreate(
            ['slug' => Str::slug($title)],
            [
                'title' => $title,
                'description' => "Kegiatan penanaman pohon di wilayah rawan karhutla, digagas oleh Wadah Pecinta Alam (Wapala) SMA Negeri 1 Ciruas bersama warga setempat. Kegiatan ini bertujuan memulihkan tutupan lahan dan menekan risiko kebakaran di musim kemarau.",
                'starts_at' => now()->addDays(14)->setTime(7, 0),
                'location' => 'Kec. Padarincang, Kabupaten Serang',
                'volunteer_quota' => 50,
                'donation_enabled' => false,
                'status' => 'terbit',
            ]
        );
    }
}
