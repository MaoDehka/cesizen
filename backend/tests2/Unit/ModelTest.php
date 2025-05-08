<?php

// namespace Tests\Unit;

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

// class ModelTest extends TestCase
// {
//     use RefreshDatabase, WithFaker;

//     /**
//      * Test de création d'un utilisateur
//      */
//     public function test_create_user()
//     {
//         // Créer un rôle
//         $role = Role::create([
//             'name' => 'user',
//             'description' => 'Utilisateur standard'
//         ]);

//         // Créer un utilisateur
//         $user = User::create([
//             'name' => 'Test User',
//             'email' => 'test@example.com',
//             'password' => Hash::make('password123'),
//             'role_id' => $role->id,
//             'active' => true,
//         ]);

//         // Vérifier que l'utilisateur a été créé
//         $this->assertDatabaseHas('users', [
//             'email' => 'test@example.com',
//             'name' => 'Test User',
//             'role_id' => $role->id,
//         ]);

//         // Vérifier la relation avec le rôle
//         $this->assertEquals('user', $user->role->name);
//     }

//     /**
//      * Test de création d'un questionnaire avec des questions
//      */
//     public function test_create_questionnaire_with_questions()
//     {
//         // Créer un questionnaire
//         $questionnaire = Questionnaire::create([
//             'title' => 'Test Questionnaire',
//             'description' => 'Description du questionnaire de test',
//             'nb_question' => 0, // Sera mis à jour automatiquement
//             'creation_date' => now(),
//             'last_modification' => now(),
//             'active' => true,
//         ]);

//         // Créer des questions pour le questionnaire
//         $questions = [];
//         for ($i = 1; $i <= 3; $i++) {
//             $questions[] = Question::create([
//                 'questionnaire_id' => $questionnaire->id,
//                 'response_text' => "Question de test $i ?",
//                 'response_score' => $i * 10,
//                 'date_response' => now(),
//             ]);
//         }

//         // Vérifier que les questions ont été créées
//         $this->assertDatabaseHas('questions', [
//             'questionnaire_id' => $questionnaire->id,
//             'response_text' => 'Question de test 1 ?',
//             'response_score' => 10,
//         ]);

//         // Vérifier la relation entre questionnaire et questions
//         $this->assertEquals(3, $questionnaire->questions()->count());
//     }

//     /**
//      * Test de création d'un niveau de stress avec des recommandations
//      */
//     public function test_create_stress_level_with_recommendations()
//     {
//         // Créer un niveau de stress
//         $stressLevel = StressLevel::create([
//             'name' => 'Modéré',
//             'min_score' => 150,
//             'max_score' => 300,
//             'risk_percentage' => 50,
//             'description' => 'Niveau de stress modéré',
//             'consequences' => 'Risque accru de problèmes de santé',
//             'active' => true,
//         ]);

//         // Créer des recommandations pour ce niveau
//         $recommendation = Recommendation::create([
//             'stress_level_id' => $stressLevel->id,
//             'description' => 'Pratiquer une activité physique régulière',
//             'details' => 'Exercice modéré 3 fois par semaine',
//             'order' => 1,
//             'active' => true,
//         ]);

//         // Vérifier que la recommandation a été créée
//         $this->assertDatabaseHas('recommendations', [
//             'stress_level_id' => $stressLevel->id,
//             'description' => 'Pratiquer une activité physique régulière',
//         ]);

//         // Vérifier la relation entre niveau de stress et recommandation
//         $this->assertEquals(1, $stressLevel->recommendations()->count());
//         $this->assertEquals('Pratiquer une activité physique régulière', 
//             $stressLevel->recommendations()->first()->description);
//     }

//     /**
//      * Test de création et détermination d'un diagnostic
//      */
//     public function test_create_diagnostic()
//     {
//         // Créer les éléments nécessaires
//         $role = Role::create(['name' => 'user', 'description' => 'Utilisateur standard']);
//         $user = User::create([
//             'name' => 'User Test',
//             'email' => 'user@test.com',
//             'password' => Hash::make('password'),
//             'role_id' => $role->id,
//             'active' => true,
//         ]);

//         $questionnaire = Questionnaire::create([
//             'title' => 'Test Questionnaire',
//             'description' => 'Description du test',
//             'nb_question' => 2,
//             'creation_date' => now(),
//             'last_modification' => now(),
//             'active' => true,
//         ]);

//         // Créer un niveau de stress
//         $stressLevel = StressLevel::create([
//             'name' => 'Modéré',
//             'min_score' => 150,
//             'max_score' => 300,
//             'risk_percentage' => 50,
//             'description' => 'Niveau de stress modéré',
//             'consequences' => 'Risque accru de problèmes de santé',
//             'active' => true,
//         ]);

//         // Créer un diagnostic
//         $diagnostic = Diagnostic::create([
//             'user_id' => $user->id,
//             'questionnaire_id' => $questionnaire->id,
//             'score_total' => 200,
//             'stress_level' => $stressLevel->name,
//             'diagnostic_date' => now(),
//             'consequences' => 'Risque accru de problèmes de santé',
//             'advices' => 'Pratiquer une activité physique régulière',
//             'saved' => false,
//         ]);

//         // Vérifier que le diagnostic a été créé
//         $this->assertDatabaseHas('diagnostics', [
//             'user_id' => $user->id,
//             'questionnaire_id' => $questionnaire->id,
//             'score_total' => 200,
//             'stress_level' => 'Modéré',
//         ]);

//         // Vérifier les relations
//         $this->assertEquals($user->id, $diagnostic->user->id);
//         $this->assertEquals($questionnaire->id, $diagnostic->questionnaire->id);
        
//         // Vérifier que le niveau de stress est correctement déterminé
//         $determinedLevel = StressLevel::determineFromScore(200);
//         $this->assertEquals('Modéré', $determinedLevel->name);
//     }

//     /**
//      * Test de création et récupération de contenu
//      */
//     public function test_content_management()
//     {
//         // Créer un contenu
//         $content = Content::create([
//             'page' => 'home',
//             'title' => 'Page d\'accueil',
//             'content' => '<p>Contenu de test pour la page d\'accueil</p>',
//             'active' => true,
//         ]);

//         // Vérifier que le contenu a été créé
//         $this->assertDatabaseHas('contents', [
//             'page' => 'home',
//             'title' => 'Page d\'accueil',
//         ]);

//         // Récupérer le contenu par page
//         $fetchedContent = Content::where('page', 'home')->where('active', true)->first();
//         $this->assertNotNull($fetchedContent);
//         $this->assertEquals('Page d\'accueil', $fetchedContent->title);
//     }
// }