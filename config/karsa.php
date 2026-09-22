<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sumber data hotspot
    |--------------------------------------------------------------------------
    */
    'hotspot' => [
        'sipongi' => [
            'url' => env('SIPONGI_HOTSPOT_URL'),
            'extra_params' => env('SIPONGI_EXTRA_PARAMS'),
            'user_agent' => env('SIPONGI_USER_AGENT', 'KARSA/1.0'),
        ],
        'firms' => [
            'map_key' => env('FIRMS_MAP_KEY'),
            'base_url' => 'https://firms.modaps.eosdis.nasa.gov/api/area/csv',
            'sources' => ['VIIRS_SNPP_NRT', 'VIIRS_NOAA20_NRT', 'VIIRS_NOAA21_NRT', 'MODIS_NRT'],
            'day_range' => 1,
        ],
        // Urutan sumber yang dicoba saat sinkronisasi (bisa ditimpa lewat tabel settings).
        // FIRMS didahulukan karena API resmi & terdokumentasi, tidak perlu verifikasi
        // DevTools seperti SiPongi+ (lihat §8.1). Semua sumber yang terkonfigurasi tetap
        // dicoba & digabung (fusi data §8.3) -- urutan ini hanya memengaruhi tampilan di
        // pengaturan admin, bukan eksklusi source lain.
        'provider_priority' => ['firms', 'sipongi'],
        'sync_interval_minutes' => 60,
        'dedup' => [
            'distance_km' => 1,
            'time_hours' => 3,
        ],
        'timeout_seconds' => 10,
        'retry_times' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Sumber data cuaca
    |--------------------------------------------------------------------------
    */
    'weather' => [
        'open_meteo' => [
            'base_url' => env('OPEN_METEO_BASE_URL', 'https://api.open-meteo.com/v1/forecast'),
        ],
        'bmkg' => [
            'base_url' => env('BMKG_BASE_URL', 'https://api.bmkg.go.id/publik/prakiraan-cuaca'),
        ],
        'timeout_seconds' => 10,
        'retry_times' => 2,
        'past_days' => 30,
        // 4 diminta ke Open-Meteo (hari ini + 3 hari) agar proyeksi 72 jam (§9) benar-benar
        // mendapat 3 hari KE DEPAN -- hari ini sendiri tidak dihitung sebagai prakiraan.
        'forecast_days' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Model risiko (nilai default, bisa ditimpa oleh risk_models aktif)
    |--------------------------------------------------------------------------
    */
    'risk' => [
        'buffer_km' => 5,
        'hotspot_max' => 10,
        'confidence_weight' => [
            'high' => 1.0,
            'medium' => 0.6,
            'low' => 0.3,
        ],
        'corroborated_multiplier' => 1.2,
        'verified_report_weight' => 1.0,
        'weather_bounds' => [
            'temp_max' => ['min' => 25, 'max' => 38],
            'rh_min' => ['min' => 85, 'max' => 35], // dibalik: 85% => 0, 35% => 100
            'wind_max' => ['min' => 0, 'max' => 40],
            'dry_days' => ['min' => 0, 'max' => 21],
        ],
        'thresholds' => [
            'rendah' => [0, 39],
            'sedang' => [40, 64],
            'tinggi' => [65, 84],
            'sangat_tinggi' => [85, 100],
        ],
        'ri_table' => [3 => 0.58, 4 => 0.90, 5 => 1.12],
        'cr_max' => 0.10,
        'alert_score_jump' => 15,
        'alert_cooldown_hours' => 12,
    ],

    /*
    |--------------------------------------------------------------------------
    | Estimasi emisi CO2 (indikatif, IPCC 2006 Vol. 4 Pers. 2.27)
    |--------------------------------------------------------------------------
    */
    'co2' => [
        'luas_per_hotspot_ha' => 1.0,
        // Nilai contoh, admin WAJIB mencocokkan dengan Tabel 2.4-2.5 IPCC 2006 Vol.4 sebelum dipakai untuk KTI.
        'default_mb' => 30.0, // t/ha
        'default_cf' => 0.5,
        'default_gef' => 1580, // g/kg CO2 untuk lahan gambut tropis (indikatif)
    ],

    /*
    |--------------------------------------------------------------------------
    | Pelaporan warga & kepercayaan
    |--------------------------------------------------------------------------
    */
    'reports' => [
        'max_photo_mb' => 5,
        'max_video_mb' => 20,
        'max_video_seconds' => 30,
        'rate_limit_per_hour_user' => 3,
        'rate_limit_per_hour_ip' => 10,
        'trust' => [
            'hotspot_nearby_km' => 5,
            'hotspot_nearby_hours' => 24,
            'exif_gps_km' => 1,
            'geolocation_accuracy_m' => 100,
            'neighbor_report_km' => 2,
            'neighbor_report_hours' => 6,
            'exif_time_hours' => 2,
            'auto_priority_threshold' => 80,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kontak & metadata
    |--------------------------------------------------------------------------
    */
    'contact_email' => env('KARSA_CONTACT_EMAIL', 'kontak@karsa.local'),

    'superadmin' => [
        'name' => env('SUPERADMIN_NAME', 'Admin KARSA'),
        'email' => env('SUPERADMIN_EMAIL'),
        'password' => env('SUPERADMIN_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Program & donasi
    |--------------------------------------------------------------------------
    */
    'donasi_enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Embed SiPongi+ (pelengkap tampilan, BUKAN sumber data -- lihat §8.4)
    |--------------------------------------------------------------------------
    | Dicek manual pada 2026-09-22: laman /peta SiPongi+ TIDAK mengirim header
    | X-Frame-Options maupun Content-Security-Policy frame-ancestors, jadi
    | iframe technically diizinkan. Tetap sediakan tautan "buka di tab baru"
    | sebagai cadangan (situs SPA pihak ketiga bisa berubah kapan saja).
    */
    'sipongi_embed_enabled' => true,
    'sipongi_public_map_url' => 'https://sipongi.gakkum.kehutanan.go.id/peta',
];
