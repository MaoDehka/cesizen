<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contenu complet de la page d'accueil
        Content::create([
            'page' => 'home',
            'title' => 'Contenu de la page d\'accueil',
            'content' => '<header class="app-header">
                <img src="../../public/logo.jpg" alt="CESIZen Logo" class="logo" />
                <h1>CESIZen</h1>
                <p>L\'application de votre santé mentale</p>
              </header>
              
              <section class="intro-section">
                <h2>Bienvenue sur CESIZen</h2>
                <p>
                  Votre plateforme dédiée à la gestion du stress et à la santé mentale.
                  Découvrez des outils et des informations pour mieux comprendre et gérer votre stress au quotidien.
                </p>
              </section>
              
              <section class="features-section">
                <h2>Nos fonctionnalités</h2>
                
                <div class="feature-card">
                  <h3>Diagnostics de stress</h3>
                  <p>Évaluez votre niveau de stress grâce à notre questionnaire basé sur l\'échelle de Holmes et Rahe.</p>
                  <a href="/questionnaires" class="feature-link">Faire le test</a>
                </div>
                
                <div class="feature-card">
                  <h3>Exercices de respiration</h3>
                  <p>Pratiquez des exercices de cohérence cardiaque pour réduire votre stress et vous relaxer.</p>
                  <a href="/breathing" class="feature-link">Commencer un exercice</a>
                </div>
                
                <div class="feature-card">
                  <h3>Tracker d\'émotions</h3>
                  <p>Suivez vos émotions au quotidien et visualisez leur évolution dans le temps.</p>
                  <a href="/emotions" class="feature-link">Accéder au tracker</a>
                </div>
              </section>
            
              <section class="info-section">
                <h2>Le stress et la santé mentale</h2>
                <p>
                  Le stress est une réaction physiologique naturelle face aux exigences de la vie quotidienne. 
                  Toutefois, lorsqu\'il devient chronique ou excessif, il peut entraîner des conséquences néfastes 
                  sur la santé mentale et physique.
                </p>
                <p>
                  Comprendre les mécanismes du stress et disposer d\'outils pour le gérer est essentiel pour 
                  maintenir un bon équilibre émotionnel et favoriser le bien-être général.
                </p>
              </section>',
            'active' => true
        ]);

        // Menu de navigation (utiliser des router-link pour conserver la fonctionnalité Vue Router)
        Content::create([
            'page' => 'menu',
            'title' => 'Menu de navigation',
            'content' => json_encode([
                [
                    'text' => 'Accueil',
                    'route' => '/',
                    'type' => 'router-link'
                ],
                [
                    'text' => 'Diagnostics',
                    'route' => '/questionnaires',
                    'type' => 'router-link'
                ],
                [
                    'text' => 'Historique',
                    'route' => '/history',
                    'type' => 'router-link'
                ],
                [
                    'text' => 'Déconnexion',
                    'route' => '#',
                    'type' => 'logout'
                ],
                [
                    'text' => 'Admin',
                    'route' => '/admin',
                    'type' => 'router-link',
                    'adminOnly' => true
                ]
            ]),
            'active' => true
        ]);

        // Pied de page
        Content::create([
            'page' => 'footer',
            'title' => 'Pied de page',
            'content' => '<p>&copy; 2025 CESIZen - L\'application de votre santé mentale</p>',
            'active' => true
        ]);
    }
}