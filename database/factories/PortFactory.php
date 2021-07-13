<?php

namespace Database\Factories;

use App\Models\Port;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Port::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ifIndex' => $this->faker->unique()->numberBetween(),
            'ifName' => $this->faker->text(20),
            'ifDescr' => $this->faker->text(255),
            'ifLastChange' => $this->faker->unixTime(),
        ];
    }
}
