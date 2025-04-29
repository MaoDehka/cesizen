<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnostic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'score_total',
        'stress_level',
        'diagnostic_date',
        'consequences',
        'advices'
    ];

    protected $casts = [
        'diagnostic_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}