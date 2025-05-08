<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class CompteUtilisateursTest extends TestCase
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
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);
    }

    /**
     * Test d'inscription d'un nouvel utilisateur (TF-CU-01)
     */
    public function testInscriptionUtilisateur()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'new@example.com',
            // Utiliser un mot de passe qui respecte les règles de validation:
            // - Au moins 12 caractères
            // - Au moins une majuscule et une minuscule
            // - Au moins un caractère spécial
            'password' => 'Password123!@#',
            'password_confirmation' => 'Password123!@#'
        ];

        $response = $this->postJson('/api/register', $userData);
        
        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'Inscription réussie']);
        
        $this->assertDatabaseHas('users', [
            'email' => 'new@example.com'
        ]);
    }

    /**
     * Test de connexion d'un utilisateur (TF-CU-02)
     */
    public function testConnexionUtilisateur()
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'Password123!@#'
        ];

        $response = $this->postJson('/api/login', $credentials);
        
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Connexion réussie'])
                 ->assertJsonStructure(['token']);
    }

    /**
     * Test de déconnexion d'un utilisateur (TF-CU-03)
     */
    public function testDeconnexionUtilisateur()
    {
        $token = $this->getAuthToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Déconnexion réussie']);
    }

    /**
     * Test de changement de mot de passe (TF-CU-04)
     */
    public function testChangementMotDePasse()
    {
        $token = $this->getAuthToken();
        
        $passwordData = [
            'current_password' => 'Password123!@#',
            'password' => 'NewPassword456!@#',
            'password_confirmation' => 'NewPassword456!@#'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/reset-password', $passwordData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Mot de passe modifié avec succès']);
                 
        // Vérifier que le nouveau mot de passe fonctionne
        $newLoginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'NewPassword456!@#'
        ]);
        
        $newLoginResponse->assertStatus(200);
    }

    /**
     * Test de tentative de connexion avec compte désactivé (TF-CU-05)
     */
    public function testConnexionCompteDesactive()
    {
        // Désactiver le compte utilisateur
        $user = User::where('email', 'test@example.com')->first();
        $user->active = false;
        $user->save();

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'Password123!@#'
        ];

        $response = $this->postJson('/api/login', $credentials);
        
        $response->assertStatus(403)
                 ->assertJsonFragment(['message' => 'Votre compte a été désactivé.']);
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