<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StressLevel;
use App\Models\Recommendation;

class StressLevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les niveaux de stress basés sur l'échelle de Holmes et Rahe
        $stressLevels = [
            [
                'name' => 'Faible',
                'min_score' => 0,
                'max_score' => 149,
                'risk_percentage' => 37,
                'description' => 'Niveau de stress faible',
                'consequences' => 'Votre niveau de stress est faible. Le risque de développer des problèmes de santé liés au stress est limité. En dessous de 150 points, le risque se révèle peu important. La somme des stress rencontrés en une année est trop peu importante pour ouvrir la voie à une maladie somatique.',
                'active' => true
            ],
            [
                'name' => 'Modéré',
                'min_score' => 150,
                'max_score' => 300,
                'risk_percentage' => 50,
                'description' => 'Niveau de stress modéré',
                'consequences' => 'Votre niveau de stress est modéré. Le risque de problèmes de santé liés au stress est accru, avec environ 50% de probabilité de développer des troubles dans les deux prochaines années. Prenez soin de vous. Ce n\'est pas la peine d\'en rajouter.',
                'active' => true
            ],
            [
                'name' => 'Élevé',
                'min_score' => 301,
                'max_score' => 1000,
                'risk_percentage' => 80,
                'description' => 'Niveau de stress élevé',
                'consequences' => 'Votre niveau de stress est élevé. Le risque de développer des problèmes de santé liés au stress est très important, avec environ 80% de probabilité de développer des troubles dans l\'année à venir. Vos risques de présenter dans un avenir proche une maladie somatique sont très élevés. Ne craignez pas de vous faire aider si c\'est votre cas.',
                'active' => true
            ]
        ];

        foreach ($stressLevels as $levelData) {
            $level = StressLevel::create($levelData);

            // Ajouter des recommandations spécifiques à chaque niveau
            if ($level->name === 'Faible') {
                $this->createRecommendations($level, [
                    [
                        'description' => 'Exercice physique régulier',
                        'details' => 'Maintenez une activité physique régulière pour conserver un bon équilibre.',
                        'order' => 1
                    ],
                    [
                        'description' => 'Alimentation équilibrée',
                        'details' => 'Privilégiez une alimentation riche en fruits, légumes et protéines maigres.',
                        'order' => 2
                    ],
                    [
                        'description' => 'Sommeil de qualité',
                        'details' => 'Assurez-vous de dormir suffisamment (7-8h par nuit) pour maintenir votre bonne santé.',
                        'order' => 3
                    ]
                ]);
            } elseif ($level->name === 'Modéré') {
                $this->createRecommendations($level, [
                    [
                        'description' => 'Exercice physique régulier',
                        'details' => 'Pratiquez une activité physique modérée au moins 3 fois par semaine pour réduire le stress.',
                        'order' => 1
                    ],
                    [
                        'description' => 'Techniques de relaxation (méditation, respiration, etc.)',
                        'details' => 'Intégrez des techniques de relaxation comme la méditation ou la respiration profonde dans votre routine quotidienne.',
                        'order' => 2
                    ],
                    [
                        'description' => 'Meilleure organisation du temps',
                        'details' => 'Structurez votre emploi du temps pour éviter la surcharge et prévoir des moments de détente.',
                        'order' => 3
                    ],
                    [
                        'description' => 'Renforcement du soutien social',
                        'details' => 'Entretenez vos relations sociales et n\'hésitez pas à demander de l\'aide à vos proches quand nécessaire.',
                        'order' => 4
                    ]
                ]);
            } else { // Niveau élevé
                $this->createRecommendations($level, [
                    [
                        'description' => 'Consultation d\'un professionnel de santé',
                        'details' => 'Il est recommandé de consulter un médecin ou un psychologue pour vous aider à gérer votre niveau de stress élevé.',
                        'order' => 1
                    ],
                    [
                        'description' => 'Exercice physique régulier',
                        'details' => 'Intégrez une activité physique adaptée à votre condition, idéalement 30 minutes par jour.',
                        'order' => 2
                    ],
                    [
                        'description' => 'Techniques de relaxation (méditation, respiration, etc.)',
                        'details' => 'Pratiquez quotidiennement des exercices de méditation, de respiration ou de relaxation musculaire progressive.',
                        'order' => 3
                    ],
                    [
                        'description' => 'Meilleure organisation du temps',
                        'details' => 'Revoyez vos priorités et apprenez à déléguer. Limitez les engagements non essentiels.',
                        'order' => 4
                    ],
                    [
                        'description' => 'Renforcement du soutien social',
                        'details' => 'Entourez-vous de personnes positives et bienveillantes. Considérez rejoindre un groupe de soutien.',
                        'order' => 5
                    ],
                    [
                        'description' => 'Alimentation équilibrée et sommeil de qualité',
                        'details' => 'Privilégiez les aliments anti-stress et établissez une routine de sommeil stricte.',
                        'order' => 6
                    ]
                ]);
            }
        }
    }

    /**
     * Créer des recommandations pour un niveau de stress
     */
    private function createRecommendations(StressLevel $level, array $recommendations): void
    {
        foreach ($recommendations as $recData) {
            Recommendation::create([
                'stress_level_id' => $level->id,
                'description' => $recData['description'],
                'details' => $recData['details'],
                'order' => $recData['order'],
                'active' => true
            ]);
        }
    }
}