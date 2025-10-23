<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotorFailureStreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotor_id',
        'streak_count',
        'last_failure_week_start',
        'last_failure_week_end',
        'last_failure_rate',
        'alert_active',
        'alert_started_week',
    ];

    protected $casts = [
        'last_failure_week_start' => 'date',
        'last_failure_week_end' => 'date',
        'alert_started_week' => 'date',
        'alert_active' => 'boolean',
        'last_failure_rate' => 'float',
    ];

    public function promotor(): BelongsTo
    {
        return $this->belongsTo(Promotor::class);
    }
}
