<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'array'];

    /**
     * Hanya nilai yang benar-benar tersimpan di DB yang di-cache selamanya --
     * fallback ke $default TIDAK di-cache, supaya perubahan default di
     * config/karsa.php langsung berlaku tanpa perlu cache:clear manual.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting:{$key}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $setting = static::find($key);

        if (! $setting) {
            return $default;
        }

        Cache::forever($cacheKey, $setting->value);

        return $setting->value;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }
}
