<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompteUtilisateursTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un rôle utilisateur pour les tests
        Role::create([
            'name' => 'user',
            'description' => 'Utilisateur standard'
        ]);
        
        Role::create([
            'name' => 'admin',
            'description' => 'Administrateur'
        ]);
    }

    /**
     * Test de création d'un utilisateur (TU-CU-01)
     */
    public function test_creation_utilisateur()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!@#',
            'password_confirmation' => 'Password123!@#'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'Inscription réussie']);
        
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        
        // Vérifier que l'utilisateur a le rôle "user"
        $user = User::where('email', 'test@example.com')->first();
        $this->assertEquals('user', $user->role->name);
    }

    /**
     * Test de validation d'email unique (TU-CU-02)
     */
    public function test_validation_email_unique()
    {
        // Créer un premier utilisateur
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);

        // Tenter de créer un second utilisateur avec le même email
        $userData = [
            'name' => 'Test User 2',
            'email' => 'test@example.com',
            'password' => 'Password123!@#',
            'password_confirmation' => 'Password123!@#'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test de confirmation de mot de passe (TU-CU-03)
     */
    public function test_confirmation_mot_de_passe()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!@#',
            'password_confirmation' => 'wrong'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /**
     * Test d'authentification réussie (TU-CU-04)
     */
    public function test_authentification_reussie()
    {
        // Créer un utilisateur
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);

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
     * Test d'authentification échouée (TU-CU-05)
     */
    public function test_authentification_echouee()
    {
        // Créer un utilisateur
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!@#'),
            'role_id' => Role::where('name', 'user')->first()->id,
            'active' => true
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(401)
                 ->assertJsonFragment(['message' => 'Email ou mot de passe incorrect.']);
    }

    /**
     * Test de déconnexion (TU-CU-06)
     */
    /**
 * Test de déconnexion (TU-CU-06)
 */
public function test_deconnexion()
{
    // Créer un utilisateur
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!@#'),
        'role_id' => Role::where('name', 'user')->first()->id,
        'active' => true
    ]);

    // Se connecter pour obtenir un token JWT
    $credentials = [
        'email' => 'test@example.com',
        'password' => 'Password123!@#'
    ];
    
    $loginResponse = $this->postJson('/api/login', $credentials);
    $token = $loginResponse->json('token');

    // Déconnexion avec le token JWT
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->postJson('/api/logout');

    $response->assertStatus(200)
             ->assertJsonFragment(['message' => 'Déconnexion réussie']);
}
/**
 * Test de changement de mot de passe (TU-CU-07)
 */
public function test_changement_mot_de_passe()
{
    // Créer un utilisateur avec un mot de passe qui répond aux exigences
    $user = User::create([
        'name' => 'Password Test User',
        'email' => 'password_test@example.com',
        'password' => Hash::make('Password123!@#'),
        'role_id' => Role::where('name', 'user')->first()->id,
        'active' => true
    ]);

    // Se connecter pour obtenir un token JWT
    $credentials = [
        'email' => 'password_test@example.com',
        'password' => 'Password123!@#'
    ];
    
    $loginResponse = $this->postJson('/api/login', $credentials);
    $token = $loginResponse->json('token');

    // Plutôt que de chercher des routes, utilisons directement l'URL connue
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

    // Vérifier que l'authentification avec le nouveau mot de passe fonctionne
    $credentials = [
        'email' => 'password_test@example.com',
        'password' => 'NewPassword456!@#'
    ];

    $loginResponse = $this->postJson('/api/login', $credentials);
    $loginResponse->assertStatus(200);
}

    /**
 * Test de désactivation d'un compte (TU-CU-08)
 */
public function test_desactivation_compte()
{
    // Créer un utilisateur administrateur
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('Password123!@#'),
        'role_id' => Role::where('name', 'admin')->first()->id,
        'active' => true
    ]);

    // Créer un utilisateur à désactiver
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('Password123!@#'),
        'role_id' => Role::where('name', 'user')->first()->id,
        'active' => true
    ]);

    // Se connecter en tant qu'admin pour obtenir un token JWT
    $credentials = [
        'email' => 'admin@example.com',
        'password' => 'Password123!@#'
    ];
    
    $loginResponse = $this->postJson('/api/login', $credentials);
    $token = $loginResponse->json('token');

    // Désactiver l'utilisateur
    $updateData = [
        'active' => false
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/users/' . $user->id, $updateData);

    $response->assertStatus(200);

    // Vérifier que l'utilisateur est désactivé
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'active' => false
    ]);

    // Vérifier que l'utilisateur ne peut plus se connecter
    $credentials = [
        'email' => 'test@example.com',
        'password' => 'Password123!@#'
    ];

    $loginResponse = $this->postJson('/api/login', $credentials);
    $loginResponse->assertStatus(403)
                 ->assertJsonFragment(['message' => 'Votre compte a été désactivé.']);
}
}