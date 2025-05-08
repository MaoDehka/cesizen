<?php

namespace Tests\NonRegression;

use App\Models\User;
use App\Models\Role;
use App\Models\Questionnaire;
use App\Models\Question;
use App\Models\Diagnostic;
use App\Models\StressLevel;
use App\Models\Recommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Carbon\Carbon;

class DiagnosticsNonRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer les rôles
        Role::create([
            'name' => 'user',
            'description' => 'Utilisateur standard'
        ]);
        
        Role::create([
            'name' => 'admin',
            'description' => 'Administrateur'
        ]);

        // Créer un utilisateur
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);

        // Créer un questionnaire
        $questionnaire = Questionnaire::create([
            'title' => 'Test Questionnaire',
            'description' => 'Description du questionnaire de test',
            'nb_question' => 3,
            'creation_date' => Carbon::now(),
            'last_modification' => Carbon::now(),
            'active' => true
        ]);

        // Créer des questions
        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'response_text' => 'Question 1',
            'response_score' => 50,
            'date_response' => Carbon::now()
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'response_text' => 'Question 2',
            'response_score' => 75,
            'date_response' => Carbon::now()
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'response_text' => 'Question 3',
            'response_score' => 100,
            'date_response' => Carbon::now()
        ]);

        // Créer les niveaux de stress
        StressLevel::create([
            'name' => 'Faible',
            'min_score' => 0,
            'max_score' => 149,
            'risk_percentage' => 37,
            'description' => 'Niveau de stress faible',
            'consequences' => 'Faibles conséquences',
            'active' => true
        ]);

        StressLevel::create([
            'name' => 'Modéré',
            'min_score' => 150,
            'max_score' => 300,
            'risk_percentage' => 50,
            'description' => 'Niveau de stress modéré',
            'consequences' => 'Conséquences modérées',
            'active' => true
        ]);

        StressLevel::create([
            'name' => 'Élevé',
            'min_score' => 301,
            'max_score' => 1000,
            'risk_percentage' => 80,
            'description' => 'Niveau de stress élevé',
            'consequences' => 'Conséquences élevées',
            'active' => true
        ]);

        // Créer un diagnostic
        Diagnostic::create([
            'user_id' => User::where('email', 'test@example.com')->first()->id,
            'questionnaire_id' => $questionnaire->id,
            'score_total' => 125,
            'stress_level' => 'Faible',
            'diagnostic_date' => Carbon::now(),
            'consequences' => 'Conséquences test',
            'advices' => 'Conseils test',
            'saved' => true
        ]);
    }

    /**
     * Vérification du calcul des scores après mise à jour (TNR-DI-01)
     */
    public function testCalculScoresApresMiseAJour()
    {
        // Récupérer l'utilisateur
        $user = User::where('email', 'test@example.com')->first();
        
        // Connexion de l'utilisateur
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $loginResponse = $this->postJson('/api/login', $credentials);
        $token = $loginResponse->json('token');
        
        // Récupérer le questionnaire et les questions
        $questionnaire = Questionnaire::first();
        $questions = Question::where('questionnaire_id', $questionnaire->id)
        ->pluck('id')
        ->toArray();

// Créer un diagnostic avant la mise à jour
$diagnosticData = [
'questionnaire_id' => $questionnaire->id,
'questions' => $questions
];

$response = $this->withHeaders([
'Authorization' => 'Bearer ' . $token,
])->postJson('/api/diagnostics', $diagnosticData);

$response->assertStatus(201);
$beforeUpdateScore = $response->json('diagnostic.score_total');
$beforeUpdateLevel = $response->json('diagnostic.stress_level');

// Simuler une mise à jour du système
$this->artisan('config:clear');
$this->artisan('cache:clear');

// Créer un nouveau diagnostic après la mise à jour avec les mêmes réponses
$response2 = $this->withHeaders([
'Authorization' => 'Bearer ' . $token,
])->postJson('/api/diagnostics', $diagnosticData);

$response2->assertStatus(201);
$afterUpdateScore = $response2->json('diagnostic.score_total');
$afterUpdateLevel = $response2->json('diagnostic.stress_level');

// Vérifier que les scores et niveaux de stress sont identiques
$this->assertEquals($beforeUpdateScore, $afterUpdateScore);
$this->assertEquals($beforeUpdateLevel, $afterUpdateLevel);
}

/**
* Vérification de l'historique des diagnostics après mise à jour (TNR-DI-02)
*/
public function testHistoriqueDiagnosticsApresMiseAJour()
{
// Récupérer l'utilisateur
$user = User::where('email', 'test@example.com')->first();

// Récupérer le diagnostic existant
$diagnostic = Diagnostic::first();

// Connexion de l'utilisateur
$credentials = [
'email' => 'test@example.com',
'password' => 'password123'
];

$loginResponse = $this->postJson('/api/login', $credentials);
$token = $loginResponse->json('token');

// Vérifier l'accès au diagnostic avant la mise à jour
$response = $this->withHeaders([
'Authorization' => 'Bearer ' . $token,
])->getJson('/api/diagnostics/' . $diagnostic->id);

$response->assertStatus(200)
->assertJsonFragment([
 'score_total' => 125,
 'stress_level' => 'Faible'
]);

// Simuler une mise à jour du système
$this->artisan('config:clear');
$this->artisan('cache:clear');

// Vérifier l'accès au diagnostic après la mise à jour
$response2 = $this->withHeaders([
'Authorization' => 'Bearer ' . $token,
])->getJson('/api/diagnostics/' . $diagnostic->id);

$response2->assertStatus(200)
->assertJsonFragment([
  'score_total' => 125,
  'stress_level' => 'Faible'
]);

// Vérifier l'accès à l'historique complet
$response3 = $this->withHeaders([
'Authorization' => 'Bearer ' . $token,
])->getJson('/api/diagnostics');

$response3->assertStatus(200)
->assertJsonCount(1)  // Il devrait y avoir au moins un diagnostic
->assertJsonFragment([
  'id' => $diagnostic->id,
  'score_total' => 125
]);
}
}