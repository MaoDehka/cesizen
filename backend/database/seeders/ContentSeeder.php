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
        // Contenu de la page d'accueil
        Content::create([
            'page' => 'home',
            'title' => 'CESIZen - L\'application de votre santé mentale',
            'content' => '<section class="intro-section">
                <h2>Bienvenue sur CESIZen</h2>
                <p>Votre plateforme dédiée à la gestion du stress et à la santé mentale.
                   Découvrez des outils et des informations pour mieux comprendre et gérer votre stress au quotidien.</p>
            </section>
            
            <section class="features-section">
                <h2>Nos fonctionnalités</h2>
                
                <div class="feature-card">
                    <h3>Diagnostics de stress</h3>
                    <p>Évaluez votre niveau de stress grâce à notre questionnaire basé sur l\'échelle de Holmes et Rahe.</p>
                </div>
                
                <div class="feature-card">
                    <h3>Exercices de respiration</h3>
                    <p>Pratiquez des exercices de cohérence cardiaque pour réduire votre stress et vous relaxer.</p>
                </div>
                
                <div class="feature-card">
                    <h3>Tracker d\'émotions</h3>
                    <p>Suivez vos émotions au quotidien et visualisez leur évolution dans le temps.</p>
                </div>
            </section>
            
            <section class="info-section">
                <h2>Le stress et la santé mentale</h2>
                <p>Le stress est une réaction physiologique naturelle face aux exigences de la vie quotidienne. 
                   Toutefois, lorsqu\'il devient chronique ou excessif, il peut entraîner des conséquences néfastes 
                   sur la santé mentale et physique.</p>
                <p>Comprendre les mécanismes du stress et disposer d\'outils pour le gérer est essentiel pour 
                   maintenir un bon équilibre émotionnel et favoriser le bien-être général.</p>
            </section>',
            'active' => true
        ]);

        // Contenu de la page À propos
        Content::create([
            'page' => 'about',
            'title' => 'À propos de CESIZen',
            'content' => '<section>
                <h2>Notre mission</h2>
                <p>CESIZen a été créé pour aider les personnes à mieux comprendre et gérer leur stress quotidien. 
                   Notre objectif est de fournir des outils accessibles et efficaces basés sur des méthodes scientifiquement validées.</p>
            </section>
            
            <section>
                <h2>Notre approche</h2>
                <p>Nous utilisons l\'échelle de stress de Holmes et Rahe, un outil reconnu internationalement pour évaluer le niveau de stress 
                   et son impact potentiel sur la santé.</p>
                <p>Cette échelle, développée en 1967 par les psychiatres Thomas Holmes et Richard Rahe, permet d\'établir le lien entre 
                   les événements stressants de la vie et le risque de développer des problèmes de santé liés au stress.</p>
            </section>
            
            <section>
                <h2>Notre équipe</h2>
                <p>CESIZen est développé par une équipe de professionnels passionnés par la santé mentale et le bien-être. 
                   Nous combinons expertise en psychologie, en technologie et en design pour créer une expérience utilisateur 
                   à la fois agréable et bénéfique.</p>
            </section>',
            'active' => true
        ]);

        // Contenu de la page Contact
        Content::create([
            'page' => 'contact',
            'title' => 'Contactez-nous',
            'content' => '<section>
                <h2>Nous contacter</h2>
                <p>Vous avez des questions, des suggestions ou des commentaires ? N\'hésitez pas à nous contacter !</p>
                
                <div class="contact-info">
                    <p><strong>Email :</strong> contact@cesizen.com</p>
                    <p><strong>Téléphone :</strong> +33 (0)1 23 45 67 89</p>
                    <p><strong>Adresse :</strong> 12 rue de la Santé, 75000 Paris, France</p>
                </div>
                
                <h3>Formulaire de contact</h3>
                <p>Vous pouvez également nous envoyer un message via le formulaire ci-dessous :</p>
                
                <!-- Formulaire de contact à implémenter -->
            </section>',
            'active' => true
        ]);
    }
}