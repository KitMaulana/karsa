<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSyncLog extends Model
{
    protected $fillable = ['source', 'started_at', 'finished_at', 'status', 'records', 'message'];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public static function log(string $source, string $status, int $records = 0, ?string $message = null): self
    {
        return static::create([
            'source' => $source,
            'started_at' => now(),
            'finished_at' => now(),
            'status' => $status,
            'records' => $records,
            'message' => $message,
        ]);
    }
}
