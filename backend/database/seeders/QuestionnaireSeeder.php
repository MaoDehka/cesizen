<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Questionnaire;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class QuestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        // Création du questionnaire de stress Holmes et Rahe
        $questionnaire = Questionnaire::create([
            'title' => 'Échelle de stress de Holmes et Rahe',
            'description' => 'Évaluez votre niveau de stress en fonction des événements vécus au cours des 12 derniers mois',
            'nb_question' => 10,
            'creation_date' => Carbon::now(),
            'last_modification' => Carbon::now(),
            'active' => true
        ]);

        // Événements de vie stressants selon l'échelle de Holmes et Rahe
        $stressEvents = [
            ['Décès du conjoint', 100],
            ['Divorce', 73],
            ['Séparation conjugale', 65],
            ['Emprisonnement', 63],
            ['Décès d\'un proche parent', 63],
            ['Blessure ou maladie personnelle', 53],
            ['Mariage', 50],
            ['Licenciement', 47],
            ['Réconciliation conjugale', 45],
            ['Retraite', 45]
        ];

        foreach ($stressEvents as $event) {
            Question::create([
                'questionnaire_id' => $questionnaire->id,
                'response_text' => $event[0],
                'response_score' => $event[1],
                'date_response' => Carbon::now()
            ]);
        }
    }
}