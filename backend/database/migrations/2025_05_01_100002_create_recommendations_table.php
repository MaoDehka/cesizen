<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stress_level_id')->constrained()->cascadeOnDelete();
            $table->string('description'); // Description de la recommandation
            $table->text('details')->nullable(); // Détails supplémentaires
            $table->integer('order')->default(0); // Ordre d'affichage
            $table->boolean('active')->default(true); // Si cette recommandation est active
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};