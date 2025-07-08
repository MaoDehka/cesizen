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
        if (!Schema::hasTable('stress_levels')) {
            Schema::create('stress_levels', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Nom du niveau (Faible, Modéré, Élevé)
                $table->integer('min_score'); // Score minimum pour ce niveau
                $table->integer('max_score'); // Score maximum pour ce niveau
                $table->integer('risk_percentage'); // Pourcentage de risque (37%, 50%, 80%)
                $table->text('description')->nullable(); // Description détaillée
                $table->text('consequences')->nullable(); // Conséquences possibles
                $table->boolean('active')->default(true); // Si ce niveau est actif
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('diagnostics', 'questionnaire_id')) {
            Schema::table('diagnostics', function (Blueprint $table) {
                $table->foreignId('questionnaire_id')->nullable()->after('user_id')
                    ->constrained()->nullOnDelete();
            });
        }

        if (!Schema::hasTable('diagnostic_responses')) {
            Schema::create('diagnostic_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('diagnostic_id')->constrained()->cascadeOnDelete();
                $table->foreignId('response_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnostic_responses');

        Schema::table('diagnostics', function (Blueprint $table) {
            if (Schema::hasColumn('diagnostics', 'questionnaire_id')) {
                $table->dropForeign(['questionnaire_id']);
                $table->dropColumn('questionnaire_id');
            }
        });

        Schema::dropIfExists('stress_levels');
    }
};