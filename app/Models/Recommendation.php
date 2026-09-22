<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = ['level', 'audience', 'icon', 'title', 'body', 'order'];

    protected $casts = [
        'order' => 'integer',
    ];
}
