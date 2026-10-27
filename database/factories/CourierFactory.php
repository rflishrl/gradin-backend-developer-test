<?php

namespace Database\Factories;

use App\Enums\VehicleType;
use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    protected $model = Courier::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '08'.fake()->numerify('##########'),
            'vehicle_type' => fake()->randomElement(VehicleType::cases()),
            'license_plate' => strtoupper(fake()->bothify('B #### ??')),
            'level' => fake()->numberBetween(Courier::MIN_LEVEL, Courier::MAX_LEVEL),
            'is_active' => true,
        ];
    }

    public function level(int $level): static
    {
        return $this->state(fn (): array => ['level' => $level]);
    }
}
