<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportStatusLog extends Model
{
    protected $fillable = ['report_id', 'from_status', 'to_status', 'changed_by', 'note'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
