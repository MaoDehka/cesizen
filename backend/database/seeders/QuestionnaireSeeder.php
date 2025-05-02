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
            'description' => 'Évaluez votre niveau de stress en fonction des événements vécus au cours des 12 derniers mois. Cette échelle, développée en 1967 par les psychiatres Thomas Holmes et Richard Rahe, permet d\'établir le lien entre stress et maladie.',
            'nb_question' => 43, // Nombre total d'événements dans l'échelle complète
            'creation_date' => Carbon::now(),
            'last_modification' => Carbon::now(),
            'active' => true
        ]);

        // Événements de vie stressants selon l'échelle complète de Holmes et Rahe
        $stressEvents = [
            ['Mort du conjoint', 100],
            ['Divorce', 73],
            ['Séparation des époux', 65],
            ['Mort d\'un parent proche', 63],
            ['Période de prison', 63],
            ['Blessure corporelle ou maladie', 53],
            ['Mariage', 50],
            ['Licenciement', 47],
            ['Réconciliation entre époux', 45],
            ['Départ à la retraite', 45],
            ['Changement dans la santé d\'un membre de la famille', 44],
            ['Grossesse', 40],
            ['Difficultés sexuelles', 39],
            ['Arrivée d\'un nouveau membre dans la famille', 39],
            ['Changement dans l\'univers du travail', 39],
            ['Changement au niveau financier', 38],
            ['Mort d\'un ami proche', 37],
            ['Changement de fonction professionnelle', 36],
            ['Modification de la fréquence des scènes de ménage', 35],
            ['Hypothèque ou emprunt de plus de 3.000 €', 31],
            ['Saisie sur hypothèque ou sur prêt', 30],
            ['Changement de responsabilité dans le travail', 29],
            ['Départ du foyer d\'une fille ou d\'un fils', 29],
            ['Difficultés avec les beaux-parents', 29],
            ['Succès exceptionnel', 28],
            ['Conjoint commençant ou cessant de travailler', 26],
            ['Début ou fin des études', 26],
            ['Changement dans les conditions de vie', 25],
            ['Changement d\'habitudes', 24],
            ['Difficultés avec son employeur/son manager', 23],
            ['Changement d\'horaires ou de conditions de travail', 20],
            ['Changement de domicile', 20],
            ['Changement de lieu d\'étude', 20],
            ['Changement dans les loisirs', 19],
            ['Changement dans les activités de la paroisse', 19],
            ['Changement dans les activités sociales', 19],
            ['Hypothèque ou emprunt de moins de 3.000€', 17],
            ['Changement dans les habitudes de sommeil', 16],
            ['Changement du nombre de réunions de famille', 15],
            ['Changement dans les habitudes alimentaires', 15],
            ['Vacances', 13],
            ['Noël', 12],
            ['Infractions mineures à la loi, contraventions', 11]
        ];

        foreach ($stressEvents as $event) {
            Question::create([
                'questionnaire_id' => $questionnaire->id,
                'response_text' => "Avez-vous vécu l'événement suivant au cours des 12 derniers mois : {$event[0]} ?",
                'response_score' => $event[1],
                'date_response' => Carbon::now()
            ]);
        }
        
        // Mise à jour du nombre de questions
        $questionnaire->update([
            'nb_question' => count($stressEvents)
        ]);
        
        $this->command->info('Questionnaire avec ' . count($stressEvents) . ' questions créé.');
    }
}