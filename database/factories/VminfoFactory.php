<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Enum\PowerState;

/** @extends Factory<\App\Models\Vminfo> */
class VminfoFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'vm_type' => $this->faker->randomElement(['vmware', 'proxmox', 'xen', 'libvirt', 'hyperv']),
            'vmwVmVMID' => (string) $this->faker->numberBetween(1, 10000),
            'vmwVmDisplayName' => $this->faker->domainWord() . '.' . $this->faker->domainName(),
            'vmwVmGuestOS' => $this->faker->randomElement([
                'debian10_64Guest',
                'debian11_64Guest',
                'ubuntu64Guest',
                'rhel8_64Guest',
                'rhel9_64Guest',
                'centos7_64Guest',
                'windows9Server64Guest',
                'windows2019srv_64Guest',
                'freebsd64Guest',
                'otherLinux64Guest',
            ]),
            'vmwVmMemSize' => $this->faker->randomElement([1024, 2048, 4096, 8192, 16384, 32768, 65536]),
            'vmwVmCpus' => $this->faker->randomElement([1, 2, 4, 8, 16, 32]),
            'vmwVmState' => $this->faker->randomElement([PowerState::OFF, PowerState::ON, PowerState::SUSPENDED, PowerState::UNKNOWN]),
        ];
    }

    public function on(): static
    {
        return $this->state(['vmwVmState' => PowerState::ON]);
    }

    public function off(): static
    {
        return $this->state(['vmwVmState' => PowerState::OFF]);
    }

    public function suspended(): static
    {
        return $this->state(['vmwVmState' => PowerState::SUSPENDED]);
    }

    public function unknown(): static
    {
        return $this->state(['vmwVmState' => PowerState::UNKNOWN]);
    }

    public function vmware(): static
    {
        return $this->state(['vm_type' => 'vmware']);
    }

    public function proxmox(): static
    {
        return $this->state(['vm_type' => 'proxmox']);
    }
}
