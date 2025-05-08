<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'password' => bcrypt('Password456!@#'),
            'role_id' => Role::where('name', 'admin')->first()->id,
            'active' => true
        ]);

        // Créer un utilisateur normal
        User::create([
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'password' => bcrypt('Password456!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);

        // Créer du contenu
        Content::create([
            'page' => 'home',
            'title' => 'Accueil',
            'content' => '<h1>Bienvenue sur CESIZen</h1><p>Contenu de la page d\'accueil</p>',
            'active' => true
        ]);

        Content::create([
            'page' => 'about',
            'title' => 'À propos',
            'content' => '<h1>À propos de CESIZen</h1><p>Contenu de la page À propos</p>',
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
     * Test d'affichage de la page d'accueil (TF-IN-01)
     */
    public function testAffichagePageAccueil()
    {
        $response = $this->getJson('/api/contents/home');
        
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'page' => 'home',
                     'title' => 'Accueil',
                     'content' => '<h1>Bienvenue sur CESIZen</h1><p>Contenu de la page d\'accueil</p>'
                 ]);
    }

    /**
     * Test de navigation vers page "À propos" (TF-IN-02)
     */
    public function testNavigationPageAbout()
    {
        $response = $this->getJson('/api/contents/about');
        
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'page' => 'about',
                     'title' => 'À propos',
                     'content' => '<h1>À propos de CESIZen</h1><p>Contenu de la page À propos</p>'
                 ]);
    }

    /**
     * Test de modification d'un contenu (admin) (TF-IN-03)
     */
    public function testModificationContenuAdmin()
    {
        // Obtenir un token admin
        $token = $this->getAdminAuthToken();
        $content = Content::where('page', 'home')->first();
        
        $updateData = [
            'title' => 'Nouveau titre d\'accueil',
            'content' => '<h1>Nouveau contenu d\'accueil</h1><p>Texte modifié</p>'
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/admin/contents/' . $content->id, $updateData);
        
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Contenu mis à jour avec succès']);
        
        // Vérifier que le contenu a été mis à jour en base de données
        $this->assertDatabaseHas('contents', [
            'id' => $content->id,
            'title' => 'Nouveau titre d\'accueil',
            'content' => '<h1>Nouveau contenu d\'accueil</h1><p>Texte modifié</p>'
        ]);
    }

    /**
     * Test de tentative d'accès à la gestion des contenus (non-admin) (TF-IN-04)
     */
    public function testAccesGestionContenuNonAdmin()
    {
        // Obtenir un token utilisateur normal
        $token = $this->getUserAuthToken();
        
        // Tenter d'accéder à la liste des contenus admin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/contents');
        
        // La réponse devrait être une erreur d'autorisation
        $response->assertStatus(403);
    }
    
    /**
     * Test de récupération d'un contenu inactif (TF-IN-05)
     */
    public function testRecuperationContenuInactif()
    {
        $response = $this->getJson('/api/contents/inactive_page');
        
        $response->assertStatus(404);
    }
    
    /**
     * Obtient un token d'authentification admin pour les tests
     *
     * @return string
     */
    private function getAdminAuthToken()
    {
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'Password456!@#'
        ]);
        
        return $loginResponse->json('token');
    }
    
    /**
     * Obtient un token d'authentification utilisateur pour les tests
     *
     * @return string
     */
    private function getUserAuthToken()
    {
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'Password456!@#'
        ]);
        
        return $loginResponse->json('token');
    }
}