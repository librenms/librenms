<?php

namespace Database\Factories;

use App\Models\Vminfo;
use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Enum\PowerState;

class VminfoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vminfo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'vm_type' => $this->faker->text(16),
            'vmwVmVMID' => $this->faker->randomDigit,
            'vmwVmDisplayName' => $this->faker->domainWord . '.' . $this->faker->domainName,
            'vmwVmGuestOS' => $this->faker->text(128),
            'vmwVmMemSize' => $this->faker->randomDigit,
            'vmwVmCpus' => $this->faker->randomDigit,
            'vmwVmState' => $this->faker->randomElement([PowerState::OFF, PowerState::ON, PowerState::SUSPENDED, PowerState::UNKNOWN]),
        ];
    }
}
