<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Role;
use App\Models\Questionnaire;
use App\Models\Question;
use App\Models\StressLevel;
use App\Models\Recommendation;
use App\Models\Diagnostic;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Carbon\Carbon;

class DiagnosticsTest extends DuskTestCase
{
    use DatabaseMigrations;

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
            'password' => bcrypt('password123'),
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
        $this->browse(function (Browser $browser) {
            // Connexion
            $browser->visit('/login')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->press('Se connecter')
                    ->waitForLocation('/')
                    
                    // Accéder à la liste des questionnaires
                    ->clickLink('Diagnostics')
                    ->waitForLocation('/questionnaires')
                    ->assertSee('Test de stress')
                    
                    // Sélectionner le questionnaire
                    ->click('.questionnaire-card')
                    ->waitFor('.question-card')
                    
                    // Répondre aux questions
                    ->click('.btn-yes') // Question 1: OUI
                    ->waitFor('.question-card')
                    ->click('.btn-no')  // Question 2: NON
                    ->waitFor('.question-card')
                    ->click('.btn-yes') // Question 3: OUI
                    
                    // Vérifier l'affichage du résultat
                    ->waitForLocation('/diagnostics/')
                    ->assertSee('Score de stress')
                    ->assertSee('Risque détecté');
        });
    }

    /**
     * Test de consultation des détails d'un diagnostic (TF-DI-02)
     */
    public function testConsultationDiagnostic()
    {
        $this->browse(function (Browser $browser) {
            // Récupérer l'ID du diagnostic existant
            $diagnostic = Diagnostic::first();

            // Connexion
            $browser->visit('/login')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->press('Se connecter')
                    ->waitForLocation('/')
                    
                    // Accéder à l'historique
                    ->clickLink('Historique')
                    ->waitForLocation('/history')
                    
                    // Consulter le diagnostic
                    ->click('.btn-view')
                    ->waitForLocation('/diagnostics/' . $diagnostic->id)
                    
                    // Vérifier l'affichage des détails
                    ->assertSee('Score de stress')
                    ->assertSee($diagnostic->score_total)
                    ->assertSee('Risque détecté : ' . $diagnostic->stress_level);
        });
    }

    /**
     * Test de sauvegarde d'un diagnostic (TF-DI-03)
     */
    public function testSauvegardeDiagnostic()
    {
        $this->browse(function (Browser $browser) {
            // Connexion
            $browser->visit('/login')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->press('Se connecter')
                    ->waitForLocation('/')
                    
                    // Accéder à la liste des questionnaires
                    ->clickLink('Diagnostics')
                    ->waitForLocation('/questionnaires')
                    
                    // Sélectionner le questionnaire
                    ->click('.questionnaire-card')
                    ->waitFor('.question-card')
                    
                    // Répondre aux questions
                    ->click('.btn-yes')
                    ->waitFor('.question-card')
                    ->click('.btn-yes')
                    ->waitFor('.question-card')
                    ->click('.btn-yes')
                    
                    // Arriver à la page de résultat
                    ->waitForLocation('/diagnostics/')
                    
                    // Sauvegarder le diagnostic
                    ->click('.btn-save')
                    ->waitForText('Résultat sauvegardé')
                    ->assertSee('Résultat sauvegardé');
        });
    }

    /**
     * Test de suppression d'un diagnostic (TF-DI-04)
     */
    public function testSuppressionDiagnostic()
    {
        $this->browse(function (Browser $browser) {
            // Connexion
            $browser->visit('/login')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->press('Se connecter')
                    ->waitForLocation('/')
                    
                    // Accéder à l'historique
                    ->clickLink('Historique')
                    ->waitForLocation('/history')
                    
                    // Supprimer le diagnostic
                    ->click('.btn-delete')
                    ->waitFor('.modal')
                    ->click('.modal .btn-delete') // Confirmer la suppression
                    
                    // Vérifier que le diagnostic a été supprimé
                    ->waitUntilMissing('.modal')
                    ->assertDontSee('Modéré');
        });
    }

    /**
     * Test d'accès aux recommandations (TF-DI-05)
     */
    public function testAccesRecommandations()
    {
        $this->browse(function (Browser $browser) {
            // Récupérer l'ID du diagnostic existant
            $diagnostic = Diagnostic::first();

            // Connexion
            $browser->visit('/login')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->press('Se connecter')
                    ->waitForLocation('/')
                    
                    // Accéder à l'historique
                    ->clickLink('Historique')
                    ->waitForLocation('/history')
                    
                    // Consulter le diagnostic
                    ->click('.btn-view')
                    ->waitForLocation('/diagnostics/' . $diagnostic->id)
                    
                    // Vérifier l'affichage des recommandations
                    ->assertSee('Solutions recommandées')
                    ->assertSee('Pratiquez une activité physique régulière')
                    ->assertSee('Adoptez des techniques de relaxation');
        });
    }
}