<?php
namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['user', 'admin']),
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the role is an admin role.
     */
    public function admin(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'admin',
                'description' => 'Administrateur du système',
            ];
        });
    }

    /**
     * Indicate that the role is a user role.
     */
    public function user(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'user',
                'description' => 'Utilisateur standard',
            ];
        });
    }
}