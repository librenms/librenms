<?php

namespace Database\Factories;

use App\Models\AlertTransport;
use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Alert\Transport;

class AlertTransportFactory extends Factory
{
    protected $model = AlertTransport::class;

    public function definition(): array
    {
        return [
            'transport_name' => $this->faker->text(30),
            'transport_type' => $this->faker->randomElement(Transport::list()),
            'is_default' => 0,
            'transport_config' => '',
        ];
    }

    public function api(
        string $options = '',
        string $method = 'get',
        string $body = '',
        string $url = 'https://librenms.org',
        string $headers = 'test=header',
        string $username = '',
        string $password = ''
    ): AlertTransportFactory {
        $config = [
            'api-method' => $method,
            'api-url' => $url,
            'api-options' => $options,
            'api-headers' => $headers,
            'api-body' => $body,
            'api-auth-username' => $username,
            'api-auth-password' => $password,
        ];

        return $this->state(function () use ($config) {
            return [
                'transport_type' => 'api',
                'transport_config' => $config,
            ];
        });
    }
}
