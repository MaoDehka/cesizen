<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Questionnaire;
use App\Models\Question;
use App\Models\StressLevel;
use App\Models\Recommendation;
use App\Models\Diagnostic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class DiagnosticsTest extends TestCase
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
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);

        // Créer un questionnaire
        $questionnaire = Questionnaire::create([
            'title' => 'Test de stress',
            'description' => 'Évaluez votre niveau de stress',
            'nb_question' => 3,
            'creation_date' => Carbon::now(),
            'last_modification' => Carbon::now(),
            'active' => true
        ]);

        // Créer des questions
        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'response_text' => 'Avez-vous eu des difficultés à dormir?',
            'response_score' => 50,
            'date_response' => Carbon::now()
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'response_text' => 'Avez-vous ressenti une fatigue persistante?',
            'response_score' => 75,
            'date_response' => Carbon::now()
        ]);

        Question::create([
            'questionnaire_id' => $questionnaire->id,
            'response_text' => 'Vous sentez-vous anxieux?',
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

        // Créer des recommandations
        $moderateLevel = StressLevel::where('name', 'Modéré')->first();
        
        Recommendation::create([
            'stress_level_id' => $moderateLevel->id,
            'description' => 'Pratiquez une activité physique régulière',
            'details' => 'Détails de la recommandation',
            'order' => 1,
            'active' => true
        ]);

        Recommendation::create([
            'stress_level_id' => $moderateLevel->id,
            'description' => 'Adoptez des techniques de relaxation',
            'details' => 'Détails de la recommandation',
            'order' => 2,
            'active' => true
        ]);

        // Créer un diagnostic existant pour les tests
        Diagnostic::create([
            'user_id' => User::where('email', 'test@example.com')->first()->id,
            'questionnaire_id' => $questionnaire->id,
            'score_total' => 150,
            'stress_level' => 'Modéré',
            'diagnostic_date' => Carbon::now()->subDays(7),
            'consequences' => 'Conséquences modérées',
            'advices' => 'Pratiquez une activité physique régulière, Adoptez des techniques de relaxation',
            'saved' => true
        ]);
    }

    /**
     * Test de réalisation d'un questionnaire de stress (TF-DI-01)
     */
    public function testRealisationQuestionnaire()
    {
        $user = User::where('email', 'test@example.com')->first();
        $token = $this->getAuthToken();
        $questionnaire = Questionnaire::first();
        
        // Récupérer les questions
        $questions = Question::where('questionnaire_id', $questionnaire->id)
                            ->pluck('id')
                            ->toArray();

        // Créer un nouveau diagnostic avec les questions du questionnaire
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/diagnostics', [
            'questionnaire_id' => $questionnaire->id,
            'questions' => [$questions[0], $questions[2]] // Répondre oui à la première et troisième question
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'diagnostic',
                     'stress_level_details',
                     'recommendations'
                 ]);
                 
        // Vérifier que le diagnostic a bien été créé
        $this->assertDatabaseHas('diagnostics', [
            'user_id' => $user->id,
            'questionnaire_id' => $questionnaire->id,
            'score_total' => 150, // 50 + 100 (les scores des questions répondues par oui)
        ]);
    }

    /**
     * Test de consultation des détails d'un diagnostic (TF-DI-02)
     */
    public function testConsultationDiagnostic()
    {
        $token = $this->getAuthToken();
        $diagnostic = Diagnostic::first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/diagnostics/' . $diagnostic->id);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'diagnostic',
                     'stress_level_details',
                     'recommendations'
                 ])
                 ->assertJsonPath('diagnostic.id', $diagnostic->id)
                 ->assertJsonPath('diagnostic.score_total', $diagnostic->score_total)
                 ->assertJsonPath('diagnostic.stress_level', $diagnostic->stress_level);
    }

    /**
     * Test de sauvegarde d'un diagnostic (TF-DI-03)
     */
    public function testSauvegardeDiagnostic()
    {
        $token = $this->getAuthToken();
        
        // Créer un nouveau diagnostic non sauvegardé
        $user = User::where('email', 'test@example.com')->first();
        $questionnaire = Questionnaire::first();
        
        $diagnostic = Diagnostic::create([
            'user_id' => $user->id,
            'questionnaire_id' => $questionnaire->id,
            'score_total' => 75,
            'stress_level' => 'Faible',
            'diagnostic_date' => Carbon::now(),
            'consequences' => 'Conséquences test',
            'advices' => 'Conseils test',
            'saved' => false
        ]);

        // Sauvegarder le diagnostic
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/diagnostics/' . $diagnostic->id . '/save');

        $response->assertStatus(200);
        
        // Vérifier que le diagnostic est maintenant sauvegardé
        $this->assertDatabaseHas('diagnostics', [
            'id' => $diagnostic->id,
            'saved' => true
        ]);
    }

    /**
     * Test de suppression d'un diagnostic (TF-DI-04)
     */
    public function testSuppressionDiagnostic()
    {
        $token = $this->getAuthToken();
        $diagnostic = Diagnostic::first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/diagnostics/' . $diagnostic->id);

        $response->assertStatus(200);
        
        // Vérifier que le diagnostic a été supprimé
        $this->assertDatabaseMissing('diagnostics', [
            'id' => $diagnostic->id
        ]);
    }

    /**
     * Test d'accès aux recommandations (TF-DI-05)
     */
    public function testAccesRecommandations()
    {
        $token = $this->getAuthToken();
        $diagnostic = Diagnostic::first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/diagnostics/' . $diagnostic->id);

        $response->assertStatus(200)
                 ->assertJsonStructure(['recommendations'])
                 ->assertJsonCount(2, 'recommendations'); // On doit avoir les 2 recommandations créées
    }
    
    /**
     * Obtient un token d'authentification pour les tests
     *
     * @return string
     */
    private function getAuthToken()
    {
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!@#'
        ]);
        
        return $loginResponse->json('token');
    }
}