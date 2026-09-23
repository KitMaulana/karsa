<?php

namespace App\Services\Ai;

use App\Enums\HotspotConfidence;
use App\Enums\RiskLevel;
use App\Models\District;
use App\Models\Hotspot;
use App\Models\RiskScore;
use App\Models\WeatherObservation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiRiskAnalyzer
{
    private ?string $apiKey;
    private string $model;
    private int $timeout;
    private int $cacheMinutes;

    public function __construct()
    {
        $this->apiKey = config('karsa.gemini.api_key');
        $this->model = config('karsa.gemini.model', 'gemini-2.5-flash');
        $this->timeout = (int) config('karsa.gemini.timeout_seconds', 15);
        $this->cacheMinutes = (int) config('karsa.gemini.cache_minutes', 60);
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Analisis prediksi risiko cerdas berbasis hotspot NASA FIRMS & cuaca.
     */
    public function analyzeRiskAndPredict(
        District $district,
        ?RiskScore $score,
        Collection $hotspots,
        ?WeatherObservation $weather
    ): ?array {
        $cacheKey = "gemini:risk_analysis:{$district->id}";

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheMinutes), function () use ($district, $score, $hotspots, $weather) {
            if (! $this->isConfigured()) {
                return $this->fallbackRiskAnalysis($district, $score, $hotspots, $weather);
            }

            $prompt = $this->buildRiskPrompt($district, $score, $hotspots, $weather);

            try {
                $response = $this->callGeminiApi($prompt);
                if (! $response) {
                    return $this->fallbackRiskAnalysis($district, $score, $hotspots, $weather);
                }

                return $response;
            } catch (Throwable $e) {
                Log::warning('GeminiRiskAnalyzer: Gagal menganalisis risiko', ['error' => $e->getMessage()]);

                return $this->fallbackRiskAnalysis($district, $score, $hotspots, $weather);
            }
        });
    }

    /**
     * Hasilkan rekomendasi aksi mitigasi taktis terpersonalisasi untuk audiens tertentu.
     */
    public function generateMitigationActions(
        District $district,
        RiskLevel $level,
        Collection $hotspots,
        ?WeatherObservation $weather,
        string $audience = 'warga_umum'
    ): ?array {
        $cacheKey = "gemini:mitigation_actions:{$district->id}:{$audience}";

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheMinutes), function () use ($district, $level, $hotspots, $weather, $audience) {
            if (! $this->isConfigured()) {
                return $this->fallbackMitigationActions($district, $level, $hotspots, $audience);
            }

            $prompt = $this->buildMitigationPrompt($district, $level, $hotspots, $weather, $audience);

            try {
                $response = $this->callGeminiApi($prompt);
                if (! $response) {
                    return $this->fallbackMitigationActions($district, $level, $hotspots, $audience);
                }

                return $response;
            } catch (Throwable $e) {
                Log::warning('GeminiRiskAnalyzer: Gagal menghasilkan rekomendasi mitigasi', ['error' => $e->getMessage()]);

                return $this->fallbackMitigationActions($district, $level, $hotspots, $audience);
            }
        });
    }

    private function callGeminiApi(string $prompt): ?array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout($this->timeout)
            ->retry(2, 500)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if (! $response->successful()) {
            Log::warning("Gemini API error HTTP {$response->status()}: " . $response->body());

            return null;
        }

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (! $text) {
            return null;
        }

        $decoded = json_decode($text, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function buildRiskPrompt(District $district, ?RiskScore $score, Collection $hotspots, ?WeatherObservation $weather): string
    {
        $hotspotCount = $hotspots->count();
        $hotspotDetails = $hotspots->map(function (Hotspot $h) {
            $confidence = $h->confidence instanceof HotspotConfidence
                ? $h->confidence->label()
                : ($h->confidence ?? 'N/A');

            return "- Satelit {$h->satellite}, Kepercayaan: {$confidence}, FRP: " . ($h->frp ?? 'N/A') . ' MW';
        })->implode("\n");

        $temp = $weather?->temp_max ?? '32';
        $rh = $weather?->rh_min ?? '50';
        $wind = $weather?->wind_max ?? '15';
        $dryDays = $weather?->dry_days ?? '5';
        $scoreVal = $score ? round($score->score, 1) : '–';
        $levelVal = $score ? ($score->level?->label() ?? 'Sedang') : 'Sedang';

        return <<<PROMPT
Anda adalah ahli analisis lingkungan dan pencegahan kebakaran hutan dan lahan (karhutla) untuk aplikasi KARSA di Kabupaten Serang, Banten, Indonesia.
Analisis data berikut yang bersumber dari satelit NASA FIRMS dan stasiun cuaca:

Wilayah: Kecamatan {$district->name}, Kabupaten Serang, Banten
Tingkat Risiko Terkini: {$levelVal} (Skor: {$scoreVal}/100)
Titik Panas Satelit NASA FIRMS (24 jam terakhir): {$hotspotCount} titik
{$hotspotDetails}

Kondisi Cuaca Hari Ini:
- Suhu Maksimum: {$temp} °C
- Kelembapan Minimum: {$rh} %
- Kecepatan Angin Maksimum: {$wind} km/jam
- Hari Tanpa Hujan Berturut-turut: {$dryDays} hari

Tugas Anda: Berikan analisis prediksi risiko kebakaran 72 jam ke depan dalam format JSON valid dengan skema:
{
  "ringkasan_situasi": "Penjelasan naratif singkat (2-3 kalimat) mengenai bahaya dari kombinasi titik panas satelit NASA dan cuaca saat ini.",
  "tingkat_ancaman": "Rendah / Waspada / Siaga / Awas",
  "faktor_kritis": ["faktor utama 1", "faktor utama 2"],
  "prediksi_72_jam": "Proyeksi kemungkinan eskalasi penyebaran api dalam 72 jam ke depan jika tidak ada intervensi.",
  "rekomendasi_utama": "Satu tindakan mitigasi paling mendesak yang harus dilakukan BPBD/warga hari ini."
}
Gunakan Bahasa Indonesia yang baku, tegas, dan mudah dipahami masyarakat.
PROMPT;
    }

    private function buildMitigationPrompt(
        District $district,
        RiskLevel $level,
        Collection $hotspots,
        ?WeatherObservation $weather,
        string $audience
    ): string {
        $audienceLabel = match ($audience) {
            'petani_pekebun' => 'Petani dan Pekebun di area ladang/perkebunan',
            'sekolah' => 'Sekolah (Guru, Siswa, dan Tenaga Pendidik)',
            default => 'Masyarakat dan Warga Umum',
        };

        $hotspotCount = $hotspots->count();
        $temp = $weather?->temp_max ?? '32';
        $wind = $weather?->wind_max ?? '15';
        $dryDays = $weather?->dry_days ?? '5';

        return <<<PROMPT
Anda adalah pakar mitigasi bencana karhutla untuk aplikasi KARSA di Kabupaten Serang, Banten.
Kondisi saat ini:
- Wilayah: Kecamatan {$district->name} (Tingkat Risiko: {$level->label()})
- Titik Panas NASA FIRMS: {$hotspotCount} titik
- Cuaca: Suhu {$temp}°C, Angin {$wind} km/jam, Hari kering {$dryDays} hari
- Target Audiens: {$audienceLabel}

Tugas Anda: Berikan panduan aksi mitigasi konkret 72 jam ke depan khusus untuk kelompok audiens di atas dalam format JSON valid:
{
  "judul_fokus": "Fokus Mitigasi Utama Hari Ini",
  "urgensi": "Rendah / Sedang / Tinggi / Sangat Mendesak",
  "langkah_aksi": [
    {
      "judul": "Langkah aksi 1",
      "deskripsi": "Instruksi praktis yang jelas",
      "prioritas": "Mendesak / Penting / Pencegahan"
    },
    {
      "judul": "Langkah aksi 2",
      "deskripsi": "Instruksi praktis yang jelas",
      "prioritas": "Mendesak / Penting / Pencegahan"
    },
    {
      "judul": "Langkah aksi 3",
      "deskripsi": "Instruksi praktis yang jelas",
      "prioritas": "Mendesak / Penting / Pencegahan"
    }
  ]
}
Gunakan Bahasa Indonesia yang aplikatif dan sesuai kearifan lokal Banten.
PROMPT;
    }

    private function fallbackRiskAnalysis(District $district, ?RiskScore $score, Collection $hotspots, ?WeatherObservation $weather): array
    {
        $count = $hotspots->count();
        $levelLabel = $score?->level?->label() ?? 'Sedang';

        if ($count > 0) {
            return [
                'ringkasan_situasi' => "Terdeteksi {$count} titik panas satelit NASA FIRMS di sekitar Kecamatan {$district->name}. Kondisi cuaca kering memerlukan kewaspadaan tinggi terhadap potensi api terbuka.",
                'tingkat_ancaman' => 'Waspada',
                'faktor_kritis' => ['Titik panas satelit aktif', 'Potensi bahan bakar kering'],
                'prediksi_72_jam' => 'Jika hembusan angin meningkat dan tidak ada hujan, potensi penjalaran api di lahan kering berisiko meluas dalam 48–72 jam.',
                'rekomendasi_utama' => 'Lakukan pengecekan lapangan (ground checking) dan hindari segala aktivitas pembakaran lahan.',
            ];
        }

        return [
            'ringkasan_situasi' => "Belum terdeteksi titik panas satelit NASA FIRMS di Kecamatan {$district->name}. Status risiko saat ini berada pada tingkat {$levelLabel}.",
            'tingkat_ancaman' => 'Terkendali',
            'faktor_kritis' => ['Kelembapan udara', 'Pemantauan vegetasi'],
            'prediksi_72_jam' => 'Kondisi diperkirakan tetap stabil selama tidak ada aktivitas pembakaran liar atau kenaikan suhu ekstrem.',
            'rekomendasi_utama' => 'Pertahankan pengawasan lingkungan dan pastikan ketersediaan sumber air cadangan.',
        ];
    }

    private function fallbackMitigationActions(District $district, RiskLevel $level, Collection $hotspots, string $audience): array
    {
        return [
            'judul_fokus' => 'Kesiapsiagaan dan Pencegahan Dini',
            'urgensi' => $level === RiskLevel::Rendah ? 'Rendah' : 'Penting',
            'langkah_aksi' => [
                [
                    'judul' => 'Patroli dan Cek Lingkungan',
                    'deskripsi' => 'Pantau area kebun dan lahan kering di sekitar pemukiman secara berkala.',
                    'prioritas' => 'Penting',
                ],
                [
                    'judul' => 'Pencegahan Titik Api',
                    'deskripsi' => 'Jangan membuang puntung rokok sembarangan dan jangan membakar sampah saat angin bertiup kencang.',
                    'prioritas' => 'Mendesak',
                ],
                [
                    'judul' => 'Kesiapan Saluran Komunikasi',
                    'deskripsi' => 'Segera laporkan indikasi kepulan asap lewat menu Pelaporan di aplikasi KARSA atau kontak darurat.',
                    'prioritas' => 'Pencegahan',
                ],
            ],
        ];
    }
}
