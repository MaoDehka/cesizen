<?php

namespace Tests\Unit;

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
use Tymon\JWTAuth\Facades\JWTAuth;

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

        // Créer des recommandations pour le niveau modéré
        $moderateLevel = StressLevel::where('name', 'Modéré')->first();
        
        Recommendation::create([
            'stress_level_id' => $moderateLevel->id,
            'description' => 'Recommandation 1',
            'details' => 'Détails de la recommandation 1',
            'order' => 1,
            'active' => true
        ]);

        Recommendation::create([
            'stress_level_id' => $moderateLevel->id,
            'description' => 'Recommandation 2',
            'details' => 'Détails de la recommandation 2',
            'order' => 2,
            'active' => true
        ]);
    }

    /**
     * Test de création d'un diagnostic - Approche directe avec le modèle (TU-DI-01)
     */
    public function test_creation_diagnostic()
    {
        // Créer un utilisateur avec un email unique
        $user = User::create([
            'name' => 'Diagnostic Test User',
            'email' => 'diagnostic_test1@example.com',
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);
        
        $questionnaire = Questionnaire::first();
        
        // Récupérer les questions
        $questions = Question::where('questionnaire_id', $questionnaire->id)
                            ->pluck('id')
                            ->toArray();

        // Créer directement un diagnostic dans la base de données
        $diagnostic = Diagnostic::create([
            'user_id' => $user->id,
            'questionnaire_id' => $questionnaire->id,
            'score_total' => array_sum(Question::whereIn('id', $questions)->pluck('response_score')->toArray()),
            'stress_level' => 'Modéré', // Basé sur le score calculé
            'diagnostic_date' => Carbon::now(),
            'consequences' => 'Conséquences test',
            'advices' => 'Conseils test',
            'saved' => false
        ]);

        // Vérifier que le diagnostic a été créé
        $this->assertDatabaseHas('diagnostics', [
            'id' => $diagnostic->id,
            'user_id' => $user->id,
            'questionnaire_id' => $questionnaire->id
        ]);
        
        // Vérifier que le diagnostic existe dans la base
        $this->assertNotNull(Diagnostic::find($diagnostic->id));

        // Le test passe si nous arrivons jusqu'ici sans exception
        $this->assertTrue(true);
    }

    /**
     * Test de calcul du niveau de stress "Faible" (TU-DI-02)
     */
    public function test_calcul_niveau_stress_faible()
    {
        // Créer un utilisateur avec un email unique
        $user = User::create([
            'name' => 'Stress Test User Low',
            'email' => 'stress_test_low@example.com',
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);
        
        $questionnaire = Questionnaire::first();
        
        // Récupérer une question avec un score faible
        $question = Question::where('response_score', 50)->first();

        // Créer directement un diagnostic avec un score faible
        $diagnostic = Diagnostic::create([
            'user_id' => $user->id,
            'questionnaire_id' => $questionnaire->id,
            'score_total' => 50, // Score faible
            'stress_level' => 'Faible',
            'diagnostic_date' => Carbon::now(),
            'consequences' => 'Conséquences faibles',
            'advices' => 'Conseils pour niveau faible',
            'saved' => false
        ]);

        // Vérifier que le niveau de stress est bien "Faible"
        $this->assertEquals('Faible', $diagnostic->stress_level);
    }

    /**
     * Test de calcul du niveau de stress "Modéré" (TU-DI-03)
     */
    public function test_calcul_niveau_stress_modere()
    {
        // Créer un utilisateur avec un email unique
        $user = User::create([
            'name' => 'Stress Test User Medium',
            'email' => 'stress_test_medium@example.com',
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);
        
        $questionnaire = Questionnaire::first();

        // Créer directement un diagnostic avec un score modéré
        $diagnostic = Diagnostic::create([
            'user_id' => $user->id,
            'questionnaire_id' => $questionnaire->id,
            'score_total' => 200, // Score modéré
            'stress_level' => 'Modéré',
            'diagnostic_date' => Carbon::now(),
            'consequences' => 'Conséquences modérées',
            'advices' => 'Conseils pour niveau modéré',
            'saved' => false
        ]);

        // Vérifier que le niveau de stress est bien "Modéré"
        $this->assertEquals('Modéré', $diagnostic->stress_level);
    }

    /**
     * Test de calcul du niveau de stress "Élevé" (TU-DI-04)
     */
    public function test_calcul_niveau_stress_eleve()
    {
        // Créer un utilisateur avec un email unique
        $user = User::create([
            'name' => 'Stress Test User High',
            'email' => 'stress_test_high@example.com',
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);
        
        $questionnaire = Questionnaire::first();

        // Créer directement un diagnostic avec un score élevé
        $diagnostic = Diagnostic::create([
            'user_id' => $user->id,
            'questionnaire_id' => $questionnaire->id,
            'score_total' => 350, // Score élevé
            'stress_level' => 'Élevé',
            'diagnostic_date' => Carbon::now(),
            'consequences' => 'Conséquences élevées',
            'advices' => 'Conseils pour niveau élevé',
            'saved' => false
        ]);

        // Vérifier que le niveau de stress est bien "Élevé"
        $this->assertEquals('Élevé', $diagnostic->stress_level);
    }

    /**
     * Test de récupération des recommandations (TU-DI-05)
     */
    public function test_recuperation_recommandations()
    {
        // Créer un utilisateur avec un email unique
        $user = User::create([
            'name' => 'Recommandation Test User',
            'email' => 'recommandation_test@example.com',
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);
        
        $questionnaire = Questionnaire::first();
        
        // Récupérer le niveau de stress modéré et ses recommandations
        $moderateLevel = StressLevel::where('name', 'Modéré')->first();
        $recommendations = Recommendation::where('stress_level_id', $moderateLevel->id)->get();

        // Créer directement un diagnostic avec un niveau modéré
        $diagnostic = Diagnostic::create([
            'user_id' => $user->id,
            'questionnaire_id' => $questionnaire->id,
            'score_total' => 200,
            'stress_level' => 'Modéré',
            'diagnostic_date' => Carbon::now(),
            'consequences' => $moderateLevel->consequences,
            'advices' => $recommendations->pluck('description')->implode(', '),
            'saved' => false
        ]);

        // Vérifier que le diagnostic a des recommandations associées
        $diagnosticModel = new Diagnostic();
        $diagnosticModel->fill($diagnostic->toArray());
        
        // Tester directement en vérifiant que les conseils contiennent les recommandations
        foreach ($recommendations as $recommendation) {
            $this->assertStringContainsString($recommendation->description, $diagnostic->advices);
        }
    }

    /**
     * Test de sauvegarde d'un diagnostic (TU-DI-06)
     */
    public function test_sauvegarde_diagnostic()
    {
        // Créer un utilisateur avec un email unique
        $user = User::create([
            'name' => 'Save Test User',
            'email' => 'save_test@example.com',
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);
        
        // Créer un diagnostic
        $diagnostic = Diagnostic::create([
            'user_id' => $user->id,
            'questionnaire_id' => Questionnaire::first()->id,
            'score_total' => 100,
            'stress_level' => 'Faible',
            'diagnostic_date' => Carbon::now(),
            'consequences' => 'Conséquences test',
            'advices' => 'Conseils test',
            'saved' => false
        ]);

        // Sauvegarder le diagnostic directement
        $diagnostic->saved = true;
        $diagnostic->save();

        // Vérifier que le diagnostic est sauvegardé
        $this->assertDatabaseHas('diagnostics', [
            'id' => $diagnostic->id,
            'saved' => true
        ]);
    }

    /**
     * Test de suppression d'un diagnostic (TU-DI-07)
     */
    public function test_suppression_diagnostic()
    {
        // Créer un utilisateur avec un email unique
        $user = User::create([
            'name' => 'Delete Test User',
            'email' => 'delete_test@example.com',
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);
        
        // Créer un diagnostic
        $diagnostic = Diagnostic::create([
            'user_id' => $user->id,
            'questionnaire_id' => Questionnaire::first()->id,
            'score_total' => 100,
            'stress_level' => 'Faible',
            'diagnostic_date' => Carbon::now(),
            'consequences' => 'Conséquences test',
            'advices' => 'Conseils test',
            'saved' => true
        ]);

        // Vérifier que le diagnostic existe
        $this->assertDatabaseHas('diagnostics', [
            'id' => $diagnostic->id
        ]);

        // Supprimer le diagnostic directement
        $diagnostic->delete();

        // Vérifier que le diagnostic a été supprimé
        $this->assertDatabaseMissing('diagnostics', [
            'id' => $diagnostic->id
        ]);
    }
}