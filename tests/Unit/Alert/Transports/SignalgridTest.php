<?php

/**
 * SignalgridTest.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Signalgrid
 */

namespace LibreNMS\Tests\Unit\Alert\Transports;

use App\Models\AlertTransport;
use App\Models\Device;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Tests\TestCase;

final class SignalgridTest extends TestCase
{
    public function testCriticalDelivery(): void
    {
        Http::fake([
            'api.signalgrid.co/*' => Http::response([
                'code' => '200',
            ]),
        ]);

        $transport = $this->transport();

        /** @var Device $mock_device */
        $mock_device = Device::factory()->make();

        $transport->deliverAlert(AlertData::testData($mock_device));

        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.signalgrid.co/v1/push'
            && $request->method() === 'POST'
            && $request->data() === [
                'client_key' => 'test-client-key',
                'channel' => 'test-channel',
                'title' => 'Testing transport from LibreNMS',
                'body' => 'This is a test alert',
                'type' => 'CRIT',
            ]);
    }

    public function testWarningDelivery(): void
    {
        Http::fake([
            'api.signalgrid.co/*' => Http::response([
                'code' => '200',
            ]),
        ]);

        $transport = $this->transport();

        /** @var Device $mock_device */
        $mock_device = Device::factory()->make();

        $alert_data = AlertData::testData($mock_device);
        $alert_data['severity'] = 'warning';

        $transport->deliverAlert($alert_data);

        Http::assertSent(fn (Request $request) => $request->data() === [
            'client_key' => 'test-client-key',
            'channel' => 'test-channel',
            'title' => 'Testing transport from LibreNMS',
            'body' => 'This is a test alert',
            'type' => 'WARN',
        ]);
    }

    public function testRecoveredDelivery(): void
    {
        Http::fake([
            'api.signalgrid.co/*' => Http::response([
                'code' => '200',
            ]),
        ]);

        $transport = $this->transport();

        /** @var Device $mock_device */
        $mock_device = Device::factory()->make();

        $alert_data = AlertData::testData($mock_device);
        $alert_data['state'] = AlertState::RECOVERED;

        $transport->deliverAlert($alert_data);

        Http::assertSent(fn (Request $request) => $request->data() === [
            'client_key' => 'test-client-key',
            'channel' => 'test-channel',
            'title' => 'Testing transport from LibreNMS',
            'body' => 'This is a test alert',
            'type' => 'SUCCESS',
        ]);
    }

    public function testApiErrorDelivery(): void
    {
        Http::fake([
            'api.signalgrid.co/*' => Http::response([
                'text' => 'Parameter error: client_key is invalid',
                'code' => '400',
            ], 200),
        ]);

        $this->expectException(AlertTransportDeliveryException::class);

        $transport = $this->transport();

        /** @var Device $mock_device */
        $mock_device = Device::factory()->make();

        $transport->deliverAlert(AlertData::testData($mock_device));
    }

    private function transport(): Transport\Signalgrid
    {
        return new Transport\Signalgrid(new AlertTransport([
            'transport_config' => [
                'signalgrid-client-key' => 'test-client-key',
                'signalgrid-channel' => 'test-channel',
            ],
        ]));
    }
}
