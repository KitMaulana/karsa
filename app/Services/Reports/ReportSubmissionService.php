<?php

namespace App\Services\Reports;

use App\Enums\ReportStatus;
use App\Models\District;
use App\Models\Hotspot;
use App\Models\Report;
use App\Models\User;
use App\Services\Geo\PointInPolygon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Alur pelaporan warga & verifikasi berlapis (CLAUDE.md §11.1). Dipakai baik
 * oleh Livewire /lapor maupun endpoint API POST /api/v1/reports (jalur
 * antrean offline dari service worker), agar logikanya konsisten satu tempat.
 */
class ReportSubmissionService
{
    public function __construct(
        private readonly MediaProcessor $mediaProcessor,
        private readonly TrustScorer $trustScorer,
    ) {}

    /**
     * @param  array{lat: float, lng: float, accuracy_m: ?float, type: string, description: string}  $input
     * @param  UploadedFile[]  $media
     */
    public function submit(User $user, array $input, array $media): Report
    {
        $district = $this->resolveDistrict($input['lat'], $input['lng']);

        $mediaMeta = [];
        $exifData = null;
        $phash = null;

        foreach ($media as $file) {
            $meta = $this->storeMedia($file);
            $mediaMeta[] = $meta;

            if ($meta['type'] === 'image' && $exifData === null) {
                $exifData = $this->mediaProcessor->extractExif($file->getRealPath());
                $phash = $this->mediaProcessor->dHash($file->getRealPath());
            }
        }

        $flags = [];
        $isDuplicate = false;

        if ($phash) {
            $recentHash = Report::whereNotNull('phash')
                ->where('created_at', '>=', now()->subDays(14))
                ->pluck('phash', 'id');

            foreach ($recentHash as $existingHash) {
                if ($this->mediaProcessor->isDuplicate($phash, $existingHash)) {
                    $isDuplicate = true;
                    break;
                }
            }
        }

        $isOutsideId = $this->trustScorer->isOutsideIndonesia($input['lat'], $input['lng']);

        $hasNearbyHotspot = Hotspot::where('detected_at', '>=', now()->subHours(24))
            ->get()
            ->contains(fn (Hotspot $h) => $this->trustScorer->distanceKm($input['lat'], $input['lng'], (float) $h->lat, (float) $h->lng) <= config('karsa.reports.trust.hotspot_nearby_km', 5));

        $exifGpsWithinRange = null;
        if ($exifData?->hasGps()) {
            $distance = $this->trustScorer->distanceKm($input['lat'], $input['lng'], $exifData->lat, $exifData->lng);
            $exifGpsWithinRange = $distance <= config('karsa.reports.trust.exif_gps_km', 1);
        }

        $exifTimeRecent = null;
        if ($exifData?->takenAt) {
            $exifTimeRecent = $exifData->takenAt->diffInHours(now()) <= config('karsa.reports.trust.exif_time_hours', 2);
        }

        $hasNeighborReport = Report::where('user_id', '!=', $user->id)
            ->where('created_at', '>=', now()->subHours(config('karsa.reports.trust.neighbor_report_hours', 6)))
            ->get()
            ->contains(fn (Report $r) => $this->trustScorer->distanceKm($input['lat'], $input['lng'], (float) $r->lat, (float) $r->lng) <= config('karsa.reports.trust.neighbor_report_km', 2));

        $verifiedCount = Report::where('user_id', $user->id)->where('status', ReportStatus::Terverifikasi->value)->count();
        $rejectedCount = Report::where('user_id', $user->id)->where('status', ReportStatus::Ditolak->value)->count();

        $recentCount = Report::where('user_id', $user->id)->where('created_at', '>=', now()->subHour())->count();
        $isNewAccount = $user->created_at->diffInHours(now()) < 24;

        $trustResult = $this->trustScorer->score(new TrustScoreInput(
            hasNearbyHotspot: $hasNearbyHotspot,
            exifGpsWithinRange: $exifGpsWithinRange,
            geolocationAccuracyM: $input['accuracy_m'] ?? null,
            hasNeighborReport: $hasNeighborReport,
            exifTimeRecent: $exifTimeRecent,
            reporterVerifiedCount: $verifiedCount,
            reporterRejectedCount: $rejectedCount,
            isDuplicatePhoto: $isDuplicate,
            isOutsideIndonesia: $isOutsideId,
            rateExceeded: $recentCount >= config('karsa.reports.rate_limit_per_hour_user', 3),
            isNewAccount: $isNewAccount,
        ));

        $status = ReportStatus::Baru;
        if ($this->trustScorer->shouldAutoPrioritize($trustResult) && $hasNearbyHotspot) {
            $status = ReportStatus::Ditinjau;
        }

        $report = Report::create([
            'user_id' => $user->id,
            'district_id' => $district?->id,
            'lat' => $input['lat'],
            'lng' => $input['lng'],
            'accuracy_m' => $input['accuracy_m'] ?? null,
            'type' => $input['type'],
            'description' => $input['description'],
            'media' => $mediaMeta,
            'exif' => $exifData ? ['lat' => $exifData->lat, 'lng' => $exifData->lng, 'taken_at' => $exifData->takenAt?->toIso8601String()] : null,
            'phash' => $phash,
            'trust_score' => $trustResult->score,
            'flags' => $trustResult->flags,
            'status' => $status->value,
        ]);

        $report->statusLogs()->create([
            'from_status' => null,
            'to_status' => $status->value,
            'changed_by' => null,
            'note' => 'Laporan dikirim oleh warga.',
        ]);

        return $report;
    }

    private function resolveDistrict(float $lat, float $lng): ?District
    {
        $withGeometry = District::whereNotNull('geometry')->get();

        foreach ($withGeometry as $district) {
            if (PointInPolygon::test($lat, $lng, $district->geometry)) {
                return $district;
            }
        }

        $bufferKm = (float) config('karsa.risk.buffer_km', 5);

        return District::whereNotNull('centroid_lat')->get()
            ->map(fn (District $d) => [
                'district' => $d,
                'distance' => $this->haversineKm($lat, $lng, (float) $d->centroid_lat, (float) $d->centroid_lng),
            ])
            ->filter(fn ($x) => $x['distance'] <= $bufferKm)
            ->sortBy('distance')
            ->first()['district'] ?? null;
    }

    /**
     * @return array{type: string, private_path: string, public_path: ?string}
     */
    private function storeMedia(UploadedFile $file): array
    {
        $isImage = str_starts_with((string) $file->getMimeType(), 'image/');
        $extension = $file->getClientOriginalExtension() ?: ($isImage ? 'jpg' : 'mp4');
        $filename = Str::uuid()->toString().'.'.$extension;

        $privatePath = $file->storeAs('reports/private', $filename, 'local');
        $publicPath = null;

        if ($isImage) {
            $publicFilename = Str::uuid()->toString().'.jpg';
            $publicPath = 'reports/public/'.$publicFilename;

            $fullPrivate = Storage::disk('local')->path($privatePath);
            $fullPublic = Storage::disk('local')->path($publicPath);

            Storage::disk('local')->makeDirectory('reports/public');
            $this->mediaProcessor->createPublicVersion($fullPrivate, $fullPublic);
        }

        return [
            'type' => $isImage ? 'image' : 'video',
            'private_path' => $privatePath,
            'public_path' => $publicPath,
        ];
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $h = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return 2 * $earthRadiusKm * asin(min(1, sqrt($h)));
    }
}
