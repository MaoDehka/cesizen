<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Diagnostic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'questionnaire_id',
        'score_total',
        'stress_level',
        'diagnostic_date',
        'consequences',
        'advices',
        'saved'
    ];

    protected $casts = [
        'diagnostic_date' => 'datetime',
        'saved' => 'boolean',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le questionnaire
     */
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    /**
     * Relation avec les réponses données
     */
    public function responses(): BelongsToMany
    {
        return $this->belongsToMany(Response::class, 'diagnostic_responses');
    }

    /**
     * Accesseur pour obtenir le niveau de stress associé
     */
    public function getStressLevelModelAttribute(): ?StressLevel
    {
        return StressLevel::where('name', $this->stress_level)->first();
    }

    /**
     * Accesseur pour obtenir les recommandations associées
     */
    public function getRecommendationsAttribute()
    {
        if ($this->stress_level_model) {
            return $this->stress_level_model->recommendations()
                ->where('active', true)
                ->orderBy('order')
                ->get();
        }
        
        return collect();
    }

    /**
     * Accesseur pour obtenir le pourcentage de risque
     */
    public function getRiskPercentageAttribute(): int
    {
        if ($this->stress_level_model) {
            return $this->stress_level_model->risk_percentage;
        }
        
        // Valeurs par défaut basées sur l'échelle de Holmes et Rahe
        if ($this->score_total < 150) {
            return 37;
        } elseif ($this->score_total <= 300) {
            return 50;
        } else {
            return 80;
        }
    }
}