<?php

// namespace Tests\Feature;

// use Tests\TestCase;
// use App\Models\User;
// use App\Models\Role;
// use App\Models\Questionnaire;
// use App\Models\Question;
// use App\Models\StressLevel;
// use App\Models\Diagnostic;
// use App\Models\Recommendation;
// use App\Models\Content;
// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\WithFaker;
// use Illuminate\Support\Facades\Hash;
// use Tymon\JWTAuth\Facades\JWTAuth;

// class ApiTest extends TestCase
// {
//     use RefreshDatabase, WithFaker;

//     protected $adminUser;
//     protected $regularUser;
//     protected $adminToken;
//     protected $userToken;

//     /**
//      * Configurer l'environnement de test
//      */
//     public function setUp(): void
//     {
//         parent::setUp();

//         // Créer les rôles
//         $adminRole = Role::create(['name' => 'admin', 'description' => 'Administrateur']);
//         $userRole = Role::create(['name' => 'user', 'description' => 'Utilisateur standard']);

//         // Créer un administrateur
//         $this->adminUser = User::create([
//             'name' => 'Admin Test',
//             'email' => 'admin@test.com',
//             'password' => Hash::make('password123'),
//             'role_id' => $adminRole->id,
//             'active' => true,
//         ]);

//         // Créer un utilisateur standard
//         $this->regularUser = User::create([
//             'name' => 'User Test',
//             'email' => 'user@test.com',
//             'password' => Hash::make('password123'),
//             'role_id' => $userRole->id,
//             'active' => true,
//         ]);

//         // Générer des tokens JWT réels pour les tests
//         $this->adminToken = JWTAuth::fromUser($this->adminUser);
//         $this->userToken = JWTAuth::fromUser($this->regularUser);
//     }

//     /**
//      * Test d'inscription utilisateur
//      */
//     public function test_user_registration()
//     {
//         $userData = [
//             'name' => 'New User',
//             'email' => 'newuser@example.com',
//             'password' => 'Password123!',
//             'password_confirmation' => 'Password123!'
//         ];

//         $response = $this->postJson('/api/register', $userData);

//         $response->assertStatus(201)
//                  ->assertJsonStructure([
//                      'user',
//                      'token',
//                      'message'
//                  ]);
                 
//         $this->assertDatabaseHas('users', [
//             'email' => 'newuser@example.com',
//             'name' => 'New User'
//         ]);
//     }

//     /**
//      * Test de connexion utilisateur
//      */
//     public function test_user_login()
//     {
//         $loginData = [
//             'email' => 'user@test.com',
//             'password' => 'password123'
//         ];

//         $response = $this->postJson('/api/login', $loginData);

//         $response->assertStatus(200)
//                  ->assertJsonStructure([
//                      'user',
//                      'token',
//                      'message'
//                  ]);
//     }

//     /**
//      * Test d'accès non autorisé à une route admin
//      */
//     public function test_unauthorized_access_to_admin_route()
//     {
//         // Utiliser un utilisateur régulier pour accéder à une route admin
//         $response = $this->actingAs($this->regularUser, 'api')
//                          ->getJson('/api/admin/stress-levels');

//         $response->assertStatus(403);
//     }

//     /**
//      * Test de récupération des questionnaires
//      */
//     public function test_fetch_questionnaires()
//     {
//         // Créer des questionnaires de test
//         Questionnaire::create([
//             'title' => 'Questionnaire 1',
//             'description' => 'Description 1',
//             'nb_question' => 0,
//             'creation_date' => now(),
//             'last_modification' => now(),
//             'active' => true,
//         ]);

//         Questionnaire::create([
//             'title' => 'Questionnaire 2',
//             'description' => 'Description 2',
//             'nb_question' => 0,
//             'creation_date' => now(),
//             'last_modification' => now(),
//             'active' => true,
//         ]);

//         // Authentifier l'utilisateur
//         $response = $this->actingAs($this->regularUser, 'api')
//                          ->getJson('/api/questionnaires');

//         $response->assertStatus(200)
//                  ->assertJsonCount(2);
//     }

//     /**
//      * Test de récupération des détails d'un questionnaire
//      */
//     public function test_fetch_questionnaire_details()
//     {
//         // Créer un questionnaire avec des questions
//         $questionnaire = Questionnaire::create([
//             'title' => 'Test Questionnaire',
//             'description' => 'Test Description',
//             'nb_question' => 0,
//             'creation_date' => now(),
//             'last_modification' => now(),
//             'active' => true,
//         ]);

//         // Ajouter des questions
//         for ($i = 1; $i <= 3; $i++) {
//             Question::create([
//                 'questionnaire_id' => $questionnaire->id,
//                 'response_text' => "Question $i",
//                 'response_score' => $i * 10,
//                 'date_response' => now(),
//             ]);
//         }

//         // Authentifier l'utilisateur
//         $response = $this->actingAs($this->regularUser, 'api')
//                          ->getJson('/api/questionnaires/' . $questionnaire->id);

//         $response->assertStatus(200)
//                  ->assertJson([
//                      'id' => $questionnaire->id,
//                      'title' => 'Test Questionnaire'
//                  ])
//                  ->assertJsonCount(3, 'questions');
//     }

//     /**
//      * Test de création d'un diagnostic
//      */
//     public function test_create_diagnostic()
//     {
//         // Créer un questionnaire avec des questions
//         $questionnaire = Questionnaire::create([
//             'title' => 'Test Questionnaire',
//             'description' => 'Test Description',
//             'nb_question' => 0,
//             'creation_date' => now(),
//             'last_modification' => now(),
//             'active' => true,
//         ]);

//         // Ajouter des questions
//         $questions = [];
//         for ($i = 1; $i <= 3; $i++) {
//             $questions[] = Question::create([
//                 'questionnaire_id' => $questionnaire->id,
//                 'response_text' => "Question $i",
//                 'response_score' => $i * 50, // Scores: 50, 100, 150
//                 'date_response' => now(),
//             ]);
//         }

//         // Créer un niveau de stress
//         StressLevel::create([
//             'name' => 'Faible',
//             'min_score' => 0,
//             'max_score' => 149,
//             'risk_percentage' => 37,
//             'description' => 'Niveau de stress faible',
//             'consequences' => 'Risque limité',
//             'active' => true,
//         ]);

//         StressLevel::create([
//             'name' => 'Modéré',
//             'min_score' => 150,
//             'max_score' => 300,
//             'risk_percentage' => 50,
//             'description' => 'Niveau de stress modéré',
//             'consequences' => 'Risque accru',
//             'active' => true,
//         ]);

//         // Données pour la création de diagnostic
//         $diagnosticData = [
//             'questionnaire_id' => $questionnaire->id,
//             'questions' => [$questions[0]->id, $questions[2]->id], // Questions 1 et 3 (score total: 200)
//         ];

//         // Authentifier l'utilisateur et créer le diagnostic
//         $response = $this->actingAs($this->regularUser, 'api')
//                          ->postJson('/api/diagnostics', $diagnosticData);

//         $response->assertStatus(201)
//                  ->assertJsonStructure([
//                      'message',
//                      'diagnostic',
//                      'stress_level_details',
//                      'recommendations'
//                  ])
//                  ->assertJson([
//                      'diagnostic' => [
//                          'score_total' => 200,
//                          'stress_level' => 'Modéré'
//                      ]
//                  ]);
//     }

//     /**
//      * Test de sauvegarde d'un diagnostic
//      */
//     public function test_save_diagnostic()
//     {
//         // Créer un diagnostic
//         $diagnostic = Diagnostic::create([
//             'user_id' => $this->regularUser->id,
//             'questionnaire_id' => null,
//             'score_total' => 150,
//             'stress_level' => 'Modéré',
//             'diagnostic_date' => now(),
//             'consequences' => 'Test consequences',
//             'advices' => 'Test advices',
//             'saved' => false,
//         ]);

//         // Authentifier l'utilisateur et sauvegarder le diagnostic
//         $response = $this->actingAs($this->regularUser, 'api')
//                          ->postJson('/api/diagnostics/' . $diagnostic->id . '/save');

//         $response->assertStatus(200)
//                  ->assertJson([
//                      'message' => 'Diagnostic sauvegardé avec succès',
//                      'diagnostic' => [
//                          'saved' => true
//                      ]
//                  ]);

//         $this->assertDatabaseHas('diagnostics', [
//             'id' => $diagnostic->id,
//             'saved' => true
//         ]);
//     }

//     /**
//      * Test de récupération des diagnostics d'un utilisateur
//      */
//     public function test_fetch_user_diagnostics()
//     {
//         // Créer des diagnostics pour l'utilisateur
//         for ($i = 1; $i <= 3; $i++) {
//             Diagnostic::create([
//                 'user_id' => $this->regularUser->id,
//                 'questionnaire_id' => null,
//                 'score_total' => $i * 50,
//                 'stress_level' => $i < 3 ? 'Faible' : 'Modéré',
//                 'diagnostic_date' => now(),
//                 'consequences' => "Test consequences $i",
//                 'advices' => "Test advices $i",
//                 'saved' => $i % 2 == 0, // Un sur deux est sauvegardé
//             ]);
//         }

//         // Authentifier l'utilisateur et récupérer ses diagnostics
//         $response = $this->actingAs($this->regularUser, 'api')
//                          ->getJson('/api/diagnostics');

//         $response->assertStatus(200)
//                  ->assertJsonCount(3);
//     }

//     /**
//      * Test d'administration - Récupération de tous les diagnostics
//      */
//     public function test_admin_fetch_all_diagnostics()
//     {
//         // Créer des diagnostics pour différents utilisateurs
//         Diagnostic::create([
//             'user_id' => $this->adminUser->id,
//             'questionnaire_id' => null,
//             'score_total' => 100,
//             'stress_level' => 'Faible',
//             'diagnostic_date' => now(),
//             'consequences' => "Admin diagnostic",
//             'advices' => "Admin advices",
//             'saved' => true,
//         ]);

//         Diagnostic::create([
//             'user_id' => $this->regularUser->id,
//             'questionnaire_id' => null,
//             'score_total' => 200,
//             'stress_level' => 'Modéré',
//             'diagnostic_date' => now(),
//             'consequences' => "User diagnostic",
//             'advices' => "User advices",
//             'saved' => true,
//         ]);

//         // Authentifier l'administrateur et récupérer tous les diagnostics
//         $response = $this->actingAs($this->adminUser, 'api')
//                          ->getJson('/api/admin/diagnostics');

//         $response->assertStatus(200)
//                  ->assertJsonCount(2);
//     }

//     /**
//      * Test d'administration - Récupération des statistiques
//      */
//     public function test_admin_fetch_statistics()
//     {
//         // Créer un questionnaire
//         $questionnaire = Questionnaire::create([
//             'title' => 'Questionnaire Stat',
//             'description' => 'Description stat',
//             'nb_question' => 0,
//             'creation_date' => now(),
//             'last_modification' => now(),
//             'active' => true,
//         ]);

//         // Créer des diagnostics pour différents utilisateurs avec différents niveaux
//         Diagnostic::create([
//             'user_id' => $this->adminUser->id,
//             'questionnaire_id' => $questionnaire->id,
//             'score_total' => 100,
//             'stress_level' => 'Faible',
//             'diagnostic_date' => now(),
//             'consequences' => "Admin diagnostic",
//             'advices' => "Admin advices",
//             'saved' => true,
//         ]);

//         Diagnostic::create([
//             'user_id' => $this->regularUser->id,
//             'questionnaire_id' => $questionnaire->id,
//             'score_total' => 200,
//             'stress_level' => 'Modéré',
//             'diagnostic_date' => now(),
//             'consequences' => "User diagnostic",
//             'advices' => "User advices",
//             'saved' => true,
//         ]);

//         // Authentifier l'administrateur et récupérer les statistiques
//         $response = $this->actingAs($this->adminUser, 'api')
//                          ->getJson('/api/admin/statistics');

//         $response->assertStatus(200)
//                  ->assertJsonStructure([
//                      'users' => ['total'],
//                      'questionnaires' => ['total'],
//                      'diagnostics' => ['total', 'saved'],
//                      'stress_levels',
//                      'questionnaire_scores'
//                  ])
//                  ->assertJson([
//                      'users' => ['total' => 2],
//                      'diagnostics' => [
//                          'total' => 2,
//                          'saved' => 2
//                      ],
//                  ]);
//     }

//     /**
//      * Test d'administration - Gestion de contenu
//      */
//     public function test_admin_content_management()
//     {
//         // Créer un contenu
//         $content = Content::create([
//             'page' => 'home',
//             'title' => 'Page d\'accueil',
//             'content' => '<p>Contenu de test pour la page d\'accueil</p>',
//             'active' => true,
//         ]);

//         // Récupérer tous les contenus en tant qu'admin
//         $response = $this->actingAs($this->adminUser, 'api')
//                          ->getJson('/api/admin/contents');

//         $response->assertStatus(200)
//                  ->assertJsonCount(1);

//         // Modifier un contenu
//         $updatedData = [
//             'title' => 'Page d\'accueil modifiée',
//             'content' => '<p>Contenu mis à jour</p>',
//             'active' => true,
//         ];

//         $response = $this->actingAs($this->adminUser, 'api')
//                          ->putJson('/api/admin/contents/' . $content->id, $updatedData);

//         $response->assertStatus(200)
//                  ->assertJson([
//                      'message' => 'Contenu mis à jour avec succès',
//                      'content' => [
//                          'title' => 'Page d\'accueil modifiée',
//                      ]
//                  ]);

//         // Vérifier l'accès public au contenu
//         $response = $this->getJson('/api/contents/home');

//         $response->assertStatus(200)
//                  ->assertJson([
//                      'title' => 'Page d\'accueil modifiée',
//                  ]);
//     }
// }