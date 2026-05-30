<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Bill> */
class BillFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'bill_name' => $this->faker->text(),
            'bill_type' => 'quota',
            'bill_day' => 1,
            'rate_95th_in' => 0,
            'rate_95th_out' => 0,
            'rate_95th' => 0,
            'dir_95th' => 'in',
            'total_data' => 0,
            'total_data_in' => 0,
            'total_data_out' => 0,
            'rate_average_in' => 0,
            'rate_average_out' => 0,
            'rate_average' => 0,
            'bill_last_calc' => now(),
            'bill_custid' => '',
            'bill_ref' => '',
            'bill_notes' => '',
            'bill_autoadded' => 0,
        ];
    }
}
