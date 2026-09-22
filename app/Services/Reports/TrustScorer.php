<?php

namespace App\Services\Reports;

/**
 * Nilai kepercayaan laporan warga (0-100), CLAUDE.md §11.1.
 * Bendera merah tidak menambah/mengurangi skor -- hanya ditampilkan ke verifikator.
 */
class TrustScorer
{
    public function score(TrustScoreInput $input): TrustScoreResult
    {
        $breakdown = [];

        $breakdown['hotspot_dekat'] = $input->hasNearbyHotspot ? 25 : 0;
        $breakdown['exif_gps_cocok'] = $input->exifGpsWithinRange === true ? 20 : 0;
        $breakdown['akurasi_gps'] = ($input->geolocationAccuracyM !== null && $input->geolocationAccuracyM <= 100) ? 15 : 0;
        $breakdown['laporan_tetangga'] = $input->hasNeighborReport ? 15 : 0;
        $breakdown['waktu_exif_baru'] = $input->exifTimeRecent === true ? 10 : 0;
        $breakdown['reputasi_pelapor'] = $this->reputationBonus($input->reporterVerifiedCount, $input->reporterRejectedCount);

        $score = min(100, max(0, array_sum($breakdown)));

        $flags = [];
        if ($input->isDuplicatePhoto) {
            $flags[] = 'foto_duplikat';
        }
        if ($input->isOutsideIndonesia) {
            $flags[] = 'lokasi_luar_indonesia';
        }
        if ($input->rateExceeded) {
            $flags[] = 'laporan_berlebih';
        }
        if ($input->isNewAccount) {
            $flags[] = 'akun_baru';
        }

        return new TrustScoreResult(round($score, 1), $breakdown, $flags);
    }

    /**
     * Reputasi pelapor 0-15: naik dengan laporan terverifikasi, turun dengan
     * laporan ditolak. Diseimbangkan agar tidak pernah negatif.
     */
    public function reputationBonus(int $verifiedCount, int $rejectedCount): float
    {
        $raw = ($verifiedCount * 3) - ($rejectedCount * 5);

        return (float) min(15, max(0, $raw));
    }

    public function shouldAutoPrioritize(TrustScoreResult $result): bool
    {
        $threshold = (float) config('karsa.reports.trust.auto_priority_threshold', 80);

        return $result->score >= $threshold;
    }

    /**
     * Uji kasar apakah koordinat berada di dalam bounding box Indonesia.
     * Bukan pengecekan poligon presisi -- cukup untuk bendera anti-penyalahgunaan.
     */
    public function isOutsideIndonesia(float $lat, float $lng): bool
    {
        return ! ($lat >= -11.5 && $lat <= 6.5 && $lng >= 94.5 && $lng <= 141.5);
    }

    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $h = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return 2 * $earthRadiusKm * asin(min(1, sqrt($h)));
    }
}
