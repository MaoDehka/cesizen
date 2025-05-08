<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Role;
use App\Models\Content;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class InformationsTest extends DuskTestCase
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

        // Créer un administrateur
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role_id' => Role::where('name', 'admin')->first()->id,
            'active' => true
        ]);

        // Créer un utilisateur normal
        User::create([
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
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
    }

    /**
     * Test d'affichage de la page d'accueil (TF-IN-01)
     */
    public function testAffichagePageAccueil()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Bienvenue sur CESIZen')
                    ->assertPresent('.dynamic-content');
        });
    }

    /**
     * Test de navigation vers page "À propos" (TF-IN-02)
     */
    public function testNavigationPageAbout()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->clickLink('À propos')
                    ->waitForLocation('/about')
                    ->assertSee('À propos de CESIZen');
        });
    }

    /**
     * Test de modification d'un contenu (admin) (TF-IN-03)
     */
    public function testModificationContenuAdmin()
    {
        $this->browse(function (Browser $browser) {
            // Connexion en tant qu'admin
            $browser->visit('/login')
                    ->type('email', 'admin@example.com')
                    ->type('password', 'password123')
                    ->press('Se connecter')
                    ->waitForLocation('/')
                    
                    // Accéder à la section admin
                    ->clickLink('Admin')
                    ->waitForLocation('/admin')
                    
                    // Aller à la gestion des contenus
                    ->click('.tab-button[data-tab="contents"]')
                    ->waitFor('.contents-tab')
                    
                    // Modifier le contenu de la page d'accueil
                    ->click('.btn-edit')
                    ->waitFor('.modal')
                    ->type('#content_title', 'Nouveau titre d\'accueil')
                    ->type('#content_html', '<h1>Nouveau contenu d\'accueil</h1><p>Texte modifié</p>')
                    ->press('Enregistrer')
                    ->waitUntilMissing('.modal')
                    
                    // Vérifier que le contenu a été mis à jour
                    ->visit('/')
                    ->assertSee('Nouveau contenu d\'accueil')
                    ->assertSee('Texte modifié');
        });
    }

    /**
     * Test de tentative d'accès à la gestion des contenus (non-admin) (TF-IN-04)
     */
    public function testAccesGestionContenuNonAdmin()
    {
        $this->browse(function (Browser $browser) {
            // Connexion en tant qu'utilisateur normal
            $browser->visit('/login')
                    ->type('email', 'user@example.com')
                    ->type('password', 'password123')
                    ->press('Se connecter')
                    ->waitForLocation('/')
                    
                    // Tenter d'accéder à la section admin
                    ->visit('/admin')
                    ->waitForLocation('/')
                    ->assertPathIs('/');
        });
    }
}