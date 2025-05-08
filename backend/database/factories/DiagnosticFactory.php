<?php
namespace Database\Factories;

use App\Models\Diagnostic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Diagnostic>
 */
class DiagnosticFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Diagnostic::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'score_total' => $this->faker->numberBetween(0, 1000),
            'stress_level' => $this->faker->randomElement(['Faible', 'Modéré', 'Élevé']),
            'diagnostic_date' => Carbon::now(),
            'consequences' => $this->faker->paragraph(),
            'advices' => $this->faker->paragraph(),
            'saved' => $this->faker->boolean(),
        ];
    }
}