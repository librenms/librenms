<?php

namespace Database\Factories;

use App\Models\AlertSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertScheduleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AlertSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->name,
            'notes' => $this->faker->text,
            'recurring' => 0,
        ];
    }

    public function recurring()
    {
        return $this->state(function () {
            return [
                'recurring' => 1,
            ];
        });
    }
}
