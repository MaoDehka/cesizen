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
        if (!Schema::hasTable('diagnostics')) {
            Schema::create('diagnostics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->integer('score_total');
                $table->string('stress_level');
                $table->dateTime('diagnostic_date');
                $table->text('consequences')->nullable();
                $table->text('advices')->nullable();
                $table->boolean('saved')->default(false);
                $table->timestamps();
            });
        }
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnostics');
    }
};