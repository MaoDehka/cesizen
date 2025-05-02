<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StressLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',             // Nom du niveau (ex: 'Faible', 'Modéré', 'Élevé')
        'min_score',        // Score minimum pour ce niveau
        'max_score',        // Score maximum pour ce niveau
        'risk_percentage',  // Pourcentage de risque associé (ex: 37%, 50%, 80%)
        'description',      // Description détaillée de ce niveau de stress
        'consequences',     // Conséquences possibles
        'active'            // Si ce niveau est actif
    ];

    protected $casts = [
        'min_score' => 'integer',
        'max_score' => 'integer',
        'risk_percentage' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Relation avec les recommandations
     */
    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    /**
     * Relation avec les diagnostics
     */
    public function diagnostics(): HasMany
    {
        return $this->hasMany(Diagnostic::class, 'stress_level', 'name');
    }

    /**
     * Détermine le niveau de stress en fonction d'un score
     */
    public static function determineFromScore(int $score): ?self
    {
        return self::where('active', true)
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();
    }
}