<?php
namespace Database\Factories;

use App\Models\StressLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StressLevel>
 */
class StressLevelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StressLevel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Faible', 'Modéré', 'Élevé']),
            'min_score' => $this->faker->numberBetween(0, 200),
            'max_score' => $this->faker->numberBetween(201, 500),
            'risk_percentage' => $this->faker->numberBetween(10, 90),
            'description' => $this->faker->paragraph(),
            'consequences' => $this->faker->paragraph(),
            'active' => true,
        ];
    }
}