<?php

namespace App\Services\Reports;

use Carbon\CarbonImmutable;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Baca EXIF (GPS & waktu) SEBELUM strip, buat versi publik tanpa EXIF
 * (resize 1280px), dan hitung perceptual hash (dHash) sederhana untuk
 * deteksi foto duplikat. CLAUDE.md §11.1 poin 2 & §19.
 */
class MediaProcessor
{
    public function __construct(
        private readonly ImageManager $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver),
    ) {}

    public function extractExif(string $path): ExifData
    {
        if (! function_exists('exif_read_data') || ! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'tiff'], true)) {
            return new ExifData;
        }

        try {
            $exif = @exif_read_data($path, 'ANY_TAG', true);
        } catch (Throwable) {
            return new ExifData;
        }

        if (! is_array($exif)) {
            return new ExifData;
        }

        $lat = $this->extractCoordinate(
            $exif['GPS']['GPSLatitude'] ?? null,
            $exif['GPS']['GPSLatitudeRef'] ?? null
        );

        $lng = $this->extractCoordinate(
            $exif['GPS']['GPSLongitude'] ?? null,
            $exif['GPS']['GPSLongitudeRef'] ?? null
        );

        $takenAt = null;
        $dateRaw = $exif['EXIF']['DateTimeOriginal'] ?? $exif['IFD0']['DateTime'] ?? null;

        if ($dateRaw) {
            try {
                $takenAt = CarbonImmutable::createFromFormat('Y:m:d H:i:s', $dateRaw);
            } catch (Throwable) {
                $takenAt = null;
            }
        }

        return new ExifData($lat, $lng, $takenAt ?: null);
    }

    /**
     * @param  array<int, string>|null  $coordinate  3 nilai rational "num/den"
     */
    private function extractCoordinate(?array $coordinate, ?string $ref): ?float
    {
        if ($coordinate === null || count($coordinate) < 3) {
            return null;
        }

        $toFloat = function (string $rational): float {
            if (! str_contains($rational, '/')) {
                return (float) $rational;
            }
            [$num, $den] = explode('/', $rational);

            return (float) $den !== 0.0 ? ((float) $num / (float) $den) : 0.0;
        };

        $degrees = $toFloat($coordinate[0]);
        $minutes = $toFloat($coordinate[1]);
        $seconds = $toFloat($coordinate[2]);

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if (in_array(strtoupper((string) $ref), ['S', 'W'], true)) {
            $decimal *= -1;
        }

        return $decimal;
    }

    /**
     * Buat versi publik: resize agar sisi terpanjang <= $maxWidth, strip EXIF
     * (proses encode ulang Intervention Image tidak menyertakan metadata).
     */
    public function createPublicVersion(string $sourcePath, string $destPath, int $maxWidth = 1280): void
    {
        $image = $this->manager->read($sourcePath);
        $image->scaleDown(width: $maxWidth);
        $image->save($destPath);
    }

    /**
     * dHash (difference hash) sederhana: grayscale, resize (size+1) x size,
     * bandingkan piksel bersebelahan. Mengembalikan string hex.
     */
    public function dHash(string $path, int $size = 8): string
    {
        $image = $this->manager->read($path)->greyscale()->resize($size + 1, $size);

        $bits = '';
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $left = $image->pickColor($x, $y)->toArray()[0];
                $right = $image->pickColor($x + 1, $y)->toArray()[0];
                $bits .= $left > $right ? '1' : '0';
            }
        }

        return str_pad(dechex((int) bindec($bits)), (int) ceil(strlen($bits) / 4), '0', STR_PAD_LEFT);
    }

    public function hammingDistance(string $hexHashA, string $hexHashB): int
    {
        $binA = base_convert($hexHashA, 16, 2);
        $binB = base_convert($hexHashB, 16, 2);

        $len = max(strlen($binA), strlen($binB));
        $binA = str_pad($binA, $len, '0', STR_PAD_LEFT);
        $binB = str_pad($binB, $len, '0', STR_PAD_LEFT);

        $distance = 0;
        for ($i = 0; $i < $len; $i++) {
            if ($binA[$i] !== $binB[$i]) {
                $distance++;
            }
        }

        return $distance;
    }

    public function isDuplicate(string $hexHashA, string $hexHashB, int $threshold = 5): bool
    {
        return $this->hammingDistance($hexHashA, $hexHashB) <= $threshold;
    }
}
