<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'stress_level_id',  // ID du niveau de stress associé
        'description',      // Description de la recommandation (ex: "Exercice physique régulier")
        'details',          // Détails supplémentaires sur la recommandation
        'order',            // Ordre d'affichage
        'active'            // Si cette recommandation est active
    ];

    protected $casts = [
        'order' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Relation avec le niveau de stress
     */
    public function stressLevel(): BelongsTo
    {
        return $this->belongsTo(StressLevel::class);
    }
}