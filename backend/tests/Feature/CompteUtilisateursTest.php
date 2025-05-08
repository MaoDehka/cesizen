<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CompteUtilisateursTest extends DuskTestCase
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
    }

    /**
     * Test d'inscription d'un nouvel utilisateur (TF-CU-01)
     */
    public function testInscriptionUtilisateur()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->type('name', 'New User')
                    ->type('email', 'new@example.com')
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->check('acceptDataPolicy')
                    ->press('S\'inscrire')
                    ->waitForLocation('/')
                    ->assertPathIs('/');
        });
    }

    /**
     * Test de connexion d'un utilisateur (TF-CU-02)
     */
    public function testConnexionUtilisateur()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->press('Se connecter')
                    ->waitForLocation('/')
                    ->assertPathIs('/');
        });
    }

    /**
     * Test de déconnexion d'un utilisateur (TF-CU-03)
     */
    public function testDeconnexionUtilisateur()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->press('Se connecter')
                    ->waitForLocation('/')
                    ->clickLink('Déconnexion')
                    ->waitForLocation('/login')
                    ->assertPathIs('/login');
        });
    }

    /**
     * Test de changement de mot de passe (TF-CU-04)
     */
    public function testChangementMotDePasse()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->press('Se connecter')
                    ->waitForLocation('/')
                    ->visit('/profile')
                    ->type('current_password', 'password123')
                    ->type('password', 'newpassword123')
                    ->type('password_confirmation', 'newpassword123')
                    ->press('Changer le mot de passe')
                    ->waitForText('Mot de passe modifié avec succès')
                    ->assertSee('Mot de passe modifié avec succès');
        });
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

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->type('email', 'test@example.com')
                    ->type('password', 'password123')
                    ->press('Se connecter')
                    ->waitForText('Votre compte a été désactivé')
                    ->assertSee('Votre compte a été désactivé');
        });
    }
}