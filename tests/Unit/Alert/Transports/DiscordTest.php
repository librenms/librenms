<?php

/**
 * DiscordTest.php
 *
 * -Description-
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2022 Juan Diego Iannelli
 * @author     Juan Diego Iannelli <jdibach@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Alert\Transports;

use App\Models\AlertTransport;
use App\Models\Device;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\Transport;
use LibreNMS\Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class DiscordTest extends TestCase
{
    public function testDiscordNoConfigDelivery(): void
    {
        Http::fake();

        $transport = new Transport\Discord(new AlertTransport([
            'transport_config' => [
                'url' => '',
                'options' => '',
                'discord-embed-fields' => '',
            ],
        ]));

        /** @var Device $mock_device */
        $mock_device = Device::factory()->make(['hostname' => 'my-hostname.com']);

        $transport->deliverAlert(AlertData::testData($mock_device));

        Http::assertSent(function (Request $request) {
            assertEquals('', $request->url());
            assertEquals('POST', $request->method());
            assertEquals('application/json', $request->header('Content-Type')[0]);
            assertEquals(
                [
                    'embeds' => [
                        [
                            'title' => '#000 Testing transport from LibreNMS',
                            'color' => 16711680,
                            'description' => 'This is a test alert',
                            'fields' => [],
                            'footer' => [
                                'text' => 'alert took 11s',
                            ],
                        ],
                    ],
                ],
                $request->data()
            );

            return true;
        });
    }

    public function testBadOptionsDelivery(): void
    {
        Http::fake();

        $transport = new Transport\Discord(new AlertTransport([
            'transport_config' => [
                'url' => 'https://discord.com/api/webhooks/number/id',
                'options' => 'multi-line options not in INIFormat' . PHP_EOL . 'are ignored',
                'discord-embed-fields' => '',
            ],
        ]));

        /** @var Device $mock_device */
        $mock_device = Device::factory()->make(['hostname' => 'my-hostname.com']);

        $transport->deliverAlert(AlertData::testData($mock_device));

        Http::assertSent(function (Request $request) {
            assertEquals('https://discord.com/api/webhooks/number/id', $request->url());
            assertEquals('POST', $request->method());
            assertEquals('application/json', $request->header('Content-Type')[0]);
            assertEquals(
                [
                    'embeds' => [
                        [
                            'title' => '#000 Testing transport from LibreNMS',
                            'color' => 16711680,
                            'description' => 'This is a test alert',
                            'fields' => [],
                            'footer' => [
                                'text' => 'alert took 11s',
                            ],
                        ],
                    ],
                ],
                $request->data()
            );

            return true;
        });
    }

    public function testBadEmbedFieldsDelivery(): void
    {
        Http::fake();

        $transport = new Transport\Discord(new AlertTransport([
            'transport_config' => [
                'url' => 'https://discord.com/api/webhooks/number/id',
                'options' => '',
                'discord-embed-fields' => 'hostname severity',
            ],
        ]));

        /** @var Device $mock_device */
        $mock_device = Device::factory()->make(['hostname' => 'my-hostname.com']);

        $transport->deliverAlert(AlertData::testData($mock_device));

        Http::assertSent(function (Request $request) {
            assertEquals('https://discord.com/api/webhooks/number/id', $request->url());
            assertEquals('POST', $request->method());
            assertEquals('application/json', $request->header('Content-Type')[0]);
            assertEquals(
                [
                    'embeds' => [
                        [
                            'title' => '#000 Testing transport from LibreNMS',
                            'color' => 16711680,
                            'description' => 'This is a test alert',
                            'fields' => [
                                [
                                    'name' => 'Hostname severity',
                                    'value' => 'Error: Invalid Field',
                                ],
                            ],
                            'footer' => [
                                'text' => 'alert took 11s',
                            ],
                        ],
                    ],
                ],
                $request->data()
            );

            return true;
        });
    }

    public function testDiscordDelivery(): void
    {
        Http::fake();

        $transport = new Transport\Discord(new AlertTransport([
            'transport_config' => [
                'url' => 'https://discord.com/api/webhooks/number/id',
                'options' => 'tts=true' . PHP_EOL . 'content=This is a text',
                'discord-embed-fields' => 'hostname,severity,wrongfield',
            ],
        ]));

        /** @var Device $mock_device */
        $mock_device = Device::factory()->make(['hostname' => 'my-hostname.com']);

        $alert_data = AlertData::testData($mock_device);

        $alert_data['msg'] = 'This test alert should not have image <img class="librenms-graph" src="google.jpeg" /> or <h2>html tags</h2></br>';

        $transport->deliverAlert($alert_data);

        Http::assertSent(function (Request $request) {
            assertEquals($request->url(), 'https://discord.com/api/webhooks/number/id');
            assertEquals($request->method(), 'POST');
            assertEquals($request->header('Content-Type')[0], 'application/json');
            assertEquals(
                [
                    'tts' => 'true',
                    'content' => 'This is a text',
                    'embeds' => [
                        [
                            'title' => '#000 Testing transport from LibreNMS',
                            'color' => 16711680,
                            'description' => 'This test alert should not have image [Image 1] or html tags',
                            'fields' => [
                                [
                                    'name' => 'Hostname',
                                    'value' => 'my-hostname.com',
                                ],
                                [
                                    'name' => 'Severity',
                                    'value' => 'critical',
                                ],
                                [
                                    'name' => 'Wrongfield',
                                    'value' => 'Error: Invalid Field',
                                ],
                            ],
                            'footer' => [
                                'text' => 'alert took 11s',
                            ],
                        ],
                        [
                            'image' => [
                                'url' => 'google.jpeg',
                            ],
                        ],
                    ],
                ],
                $request->data()
            );

            return true;
        });
    }
}
