<?php

namespace Tests\Unit;

use App\Models\Content;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InformationsTest extends TestCase
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

        // Créer un administrateur
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'admin')->first()->id,
            'active' => true
        ]);

        // Créer un utilisateur normal
        User::create([
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);

        // Créer du contenu pour les tests
        Content::create([
            'page' => 'home',
            'title' => 'Accueil',
            'content' => '<h1>Bienvenue sur CESIZen</h1><p>Contenu de la page d\'accueil</p>',
            'active' => true
        ]);

        Content::create([
            'page' => 'inactive_page',
            'title' => 'Page Inactive',
            'content' => '<h1>Cette page est inactive</h1>',
            'active' => false
        ]);
    }

    /**
     * Test de récupération d'un contenu (TU-IN-01)
     */
    public function test_recuperation_contenu()
    {
        $response = $this->getJson('/api/contents/home');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'page' => 'home',
                     'title' => 'Accueil'
                 ]);
    }

    /**
     * Test de récupération d'un contenu inexistant (TU-IN-02)
     */
    public function test_recuperation_contenu_inexistant()
    {
        $response = $this->getJson('/api/contents/nonexistent');

        $response->assertStatus(404);
    }

    /**
     * Test de mise à jour d'un contenu (TU-IN-03)
     */
    public function test_mise_a_jour_contenu()
{
    // Récupérer l'admin
    $admin = User::where('email', 'admin@example.com')->first();
    
    // Récupérer le contenu
    $content = Content::where('page', 'home')->first();

    // Se connecter en tant qu'admin pour obtenir un token JWT
    $credentials = [
        'email' => 'admin@example.com',
        'password' => 'Password123!@#'
    ];
    
    $loginResponse = $this->postJson('/api/login', $credentials);
    $token = $loginResponse->json('token');

    $updateData = [
        'title' => 'Nouveau Titre',
        'content' => 'Nouveau contenu'
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/admin/contents/' . $content->id, $updateData);

    $response->assertStatus(200)
             ->assertJsonFragment(['message' => 'Contenu mis à jour avec succès']);

    // Vérifier que le contenu a été mis à jour
    $this->assertDatabaseHas('contents', [
        'id' => $content->id,
        'title' => 'Nouveau Titre',
        'content' => 'Nouveau contenu'
    ]);
}

    /**
     * Test de récupération d'un contenu inactif (TU-IN-04)
     */
    public function test_recuperation_contenu_inactif()
    {
        $response = $this->getJson('/api/contents/inactive_page');

        $response->assertStatus(404);
    }
}