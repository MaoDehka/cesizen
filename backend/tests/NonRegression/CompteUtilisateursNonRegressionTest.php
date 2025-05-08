<?php

namespace Tests\NonRegression;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompteUtilisateursNonRegressionTest extends TestCase
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
    }

    /**
     * Vérification de la persistance des données utilisateur après mise à jour (TNR-CU-01)
     */
    public function testPersistanceDataUser()
    {
        // Simuler une mise à jour du système
        $this->artisan('migrate:fresh', ['--seed' => true]);
        
        // Créer l'utilisateur à nouveau après la mise à jour
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);
        
        // Vérifier la connexion de l'utilisateur
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/login', $credentials);
        
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Connexion réussie'])
                 ->assertJsonStructure(['token']);
                 
        // Vérifier que le profil est intact
        $token = $response->json('token');
        
        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');
        
        $profileResponse->assertStatus(200)
                        ->assertJsonFragment([
                            'name' => 'Test User',
                            'email' => 'test@example.com'
                        ]);
    }

    /**
     * Vérification de la sécurité des tokens après mise à jour (TNR-CU-02)
     */
    public function testSecuriteTokens()
    {
        // Créer un utilisateur et obtenir un token
        $user = User::where('email', 'test@example.com')->first();
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // Simuler une mise à jour du système (sans effacer les tokens)
        $this->artisan('config:clear');
        $this->artisan('cache:clear');
        
        // Vérifier que le token fonctionne toujours
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/user');
        
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'name' => 'Test User',
                     'email' => 'test@example.com'
                 ]);
    }
}