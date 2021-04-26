<?php
/**
 * SmokepingCliTest.php
 *
 * Checks that smokeping configuration output is consistent
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
 * @link       https://librenms.org
 * @copyright  2020 Adam Bishop
 * @author     Adam Bishop <adam@omega.org.uk>
 */

namespace LibreNMS\Tests;

use App\Console\Commands\SmokepingGenerateCommand;
use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SmokepingCliTest extends DBTestCase
{
    use DatabaseTransactions;

    protected $groups = [
        'Le23HKVMvN' => [
            'Cl09bZU4sn' => [
                'transport' => 'udp',
            ],
            'c559TvthzY' => [
                'transport' => 'udp6',
            ],
            'sNtzSdxdw8' => [
                'transport' => 'udp6',
            ],
            '10.0.0.3' => [
                'transport' => 'udp',
            ],
            '2600::' => [
                'transport' => 'udp',
            ],
        ],
        'Psv9oZcxdC' => [
            'oHiPfLzrmU' => [
                'transport' => 'udp',
            ],
            'kEn7hZ7N37' => [
                'transport' => 'udp6',
            ],
            'PcbZ5FKtS3' => [
                'transport' => 'udp6',
            ],
            '192.168.1.1' => [
                'transport' => 'udp',
            ],
            'fe80::' => [
                'transport' => 'udp',
            ],
        ],
        '4diY0pWFik' => [
            'example.org' => [
                'transport' => 'udp',
            ],
            'host_with_under_score.example.org' => [
                'transport' => 'udp6',
            ],
        ],
    ];

    private $instance = null;

    protected function setUp(): void
    {
        // We need an app instance available for these tests to load the translation machinary
        $this->app = $this->createApplication();

        $this->instance = new SmokePingGenerateCommand();
        $this->instance->disableDNSLookup();
        parent::setUp();
    }

    public function testNonsense()
    {
        $this->assertNotEquals(0, \Artisan::call('smokeping:generate --probes --targets --no-header'));
        $this->assertNotEquals(0, \Artisan::call('smokeping:generate --probes --targets --single-process'));
        $this->assertNotEquals(0, \Artisan::call('smokeping:generate --probes --targets'));
        $this->assertNotEquals(0, \Artisan::call('smokeping:generate --no-header'));
        $this->assertNotEquals(0, \Artisan::call('smokeping:generate --single-process'));
        $this->assertNotEquals(0, \Artisan::call('smokeping:generate'));

        $this->expectException('RuntimeException');
        \Artisan::call('smokeping:generate --foobar');
    }

    public function testBuildHeader()
    {
        $warnings = ['rpPvjwdI0M0hlg6ZgZA', '2aUjOMql6ZWN7H0DthWDOyCvkXs0kVShhnASnc', 'HYMWbDplSW9PLNK9o9tySeJF4Ac61uTRHUUxxBXHiCl'];

        $this->instance->setWarning($warnings[0]);
        $this->instance->setWarning($warnings[1]);
        $this->instance->setWarning($warnings[2]);

        $header = $this->instance->buildHeader(false, false);

        $this->assertEmpty(array_pop($header));

        foreach ($header as $line) {
            $this->assertTrue(Str::startsWith($line, '# '), $line);
            $this->assertTrue(Str::contains($line, array_merge($warnings, [__('commands.smokeping:generate.header-first'), __('commands.smokeping:generate.header-second'), __('commands.smokeping:generate.header-third')])), $line);
        }

        $this->assertEquals($this->instance->buildHeader(true, false), []);
    }

    public function testAssembleProbes()
    {
        $tests = [0, -1];

        foreach ($tests as $test) {
            $this->assertEmpty($this->instance->assembleProbes($test));
        }
    }

    public function testBuildProbe()
    {
        $saved = ['+ Pl0JnP2vfE',
            '  binary = /usr/bin/G28F3fFeew',
            '  blazemode = true',
            '++ Xq93BufZAU',
            '++ etzY41dSRj0',
            '++ etzY41dSRj1',
            '++ etzY41dSRj2',
            '',
        ];

        $output = $this->instance->buildProbes('Pl0JnP2vfE', 'Xq93BufZAU', 'etzY41dSRj', '/usr/bin/G28F3fFeew', 3);

        $this->assertEquals(implode(PHP_EOL, $saved), implode(PHP_EOL, $output));
    }

    public function testBuildTargets()
    {
        $saved = [
            '+ Le23HKVMvN',
            '  menu = Le23HKVMvN',
            '  title = Le23HKVMvN',
            '',
            '++ Cl09bZU4sn',
            '   menu = Cl09bZU4sn',
            '   title = Cl09bZU4sn',
            '   probe = lnmsFPing-0',
            '   host = Cl09bZU4sn',
            '',
            '++ c559TvthzY',
            '   menu = c559TvthzY',
            '   title = c559TvthzY',
            '   probe = lnmsFPing6-0',
            '   host = c559TvthzY',
            '',
            '++ sNtzSdxdw8',
            '   menu = sNtzSdxdw8',
            '   title = sNtzSdxdw8',
            '   probe = lnmsFPing6-1',
            '   host = sNtzSdxdw8',
            '',
            '++ 10_0_0_3',
            '   menu = 10.0.0.3',
            '   title = 10.0.0.3',
            '   probe = lnmsFPing-1',
            '   host = 10.0.0.3',
            '',
            '++ 2600::',
            '   menu = 2600::',
            '   title = 2600::',
            '   probe = lnmsFPing-2',
            '   host = 2600::',
            '',
            '+ Psv9oZcxdC',
            '  menu = Psv9oZcxdC',
            '  title = Psv9oZcxdC',
            '',
            '++ oHiPfLzrmU',
            '   menu = oHiPfLzrmU',
            '   title = oHiPfLzrmU',
            '   probe = lnmsFPing-3',
            '   host = oHiPfLzrmU',
            '',
            '++ kEn7hZ7N37',
            '   menu = kEn7hZ7N37',
            '   title = kEn7hZ7N37',
            '   probe = lnmsFPing6-2',
            '   host = kEn7hZ7N37',
            '',
            '++ PcbZ5FKtS3',
            '   menu = PcbZ5FKtS3',
            '   title = PcbZ5FKtS3',
            '   probe = lnmsFPing6-3',
            '   host = PcbZ5FKtS3',
            '',
            '++ 192_168_1_1',
            '   menu = 192.168.1.1',
            '   title = 192.168.1.1',
            '   probe = lnmsFPing-0',
            '   host = 192.168.1.1',
            '',
            '++ fe80::',
            '   menu = fe80::',
            '   title = fe80::',
            '   probe = lnmsFPing-1',
            '   host = fe80::',
            '',
            '+ 4diY0pWFik',
            '  menu = 4diY0pWFik',
            '  title = 4diY0pWFik',
            '',
            '++ example_org',
            '   menu = example.org',
            '   title = example.org',
            '   probe = lnmsFPing-2',
            '   host = example.org',
            '',
            '++ host_with_under_score_example_org',
            '   menu = host_with_under_score.example.org',
            '   title = host_with_under_score.example.org',
            '   probe = lnmsFPing6-0',
            '   host = host_with_under_score.example.org',
            '',
        ];

        $output = $this->instance->buildTargets($this->groups, 4, false);

        $this->assertEquals(implode(PHP_EOL, $saved), implode(PHP_EOL, $output));
    }

    public function testSingleProccess()
    {
        $saved = [
            '+ Le23HKVMvN',
            '  menu = Le23HKVMvN',
            '  title = Le23HKVMvN',
            '',
            '++ Cl09bZU4sn',
            '   menu = Cl09bZU4sn',
            '   title = Cl09bZU4sn',
            '   host = Cl09bZU4sn',
            '',
            '++ c559TvthzY',
            '   menu = c559TvthzY',
            '   title = c559TvthzY',
            '   host = c559TvthzY',
            '',
            '++ sNtzSdxdw8',
            '   menu = sNtzSdxdw8',
            '   title = sNtzSdxdw8',
            '   host = sNtzSdxdw8',
            '',
            '++ 10_0_0_3',
            '   menu = 10.0.0.3',
            '   title = 10.0.0.3',
            '   host = 10.0.0.3',
            '',
            '++ 2600::',
            '   menu = 2600::',
            '   title = 2600::',
            '   host = 2600::',
            '',
            '+ Psv9oZcxdC',
            '  menu = Psv9oZcxdC',
            '  title = Psv9oZcxdC',
            '',
            '++ oHiPfLzrmU',
            '   menu = oHiPfLzrmU',
            '   title = oHiPfLzrmU',
            '   host = oHiPfLzrmU',
            '',
            '++ kEn7hZ7N37',
            '   menu = kEn7hZ7N37',
            '   title = kEn7hZ7N37',
            '   host = kEn7hZ7N37',
            '',
            '++ PcbZ5FKtS3',
            '   menu = PcbZ5FKtS3',
            '   title = PcbZ5FKtS3',
            '   host = PcbZ5FKtS3',
            '',
            '++ 192_168_1_1',
            '   menu = 192.168.1.1',
            '   title = 192.168.1.1',
            '   host = 192.168.1.1',
            '',
            '++ fe80::',
            '   menu = fe80::',
            '   title = fe80::',
            '   host = fe80::',
            '',
            '+ 4diY0pWFik',
            '  menu = 4diY0pWFik',
            '  title = 4diY0pWFik',
            '',
            '++ example_org',
            '   menu = example.org',
            '   title = example.org',
            '   host = example.org',
            '',
            '++ host_with_under_score_example_org',
            '   menu = host_with_under_score.example.org',
            '   title = host_with_under_score.example.org',
            '   host = host_with_under_score.example.org',
            '',
        ];

        $output = $this->instance->buildTargets($this->groups, 4, true);

        $this->assertEquals(implode(PHP_EOL, $saved), implode(PHP_EOL, $output));
    }

    public function testCompareLegacy()
    {
        $data = [];

        // Generate a ridiculous number of random devices for testing
        foreach (range(1, 1000) as $i) {
            $device = Device::factory()->create();
            $data[$device->type][] = $device->hostname;
        }

        // Sort the data so the output matches the one from the database
        $data = Arr::sortRecursive($data);

        // Disable DNS lookups
        \Artisan::call('smokeping:generate --targets --no-header --no-dns --single-process --compat');
        $new = \Artisan::Output();
        $old = $this->legacyAlgo($data);

        $this->assertEquals($this->canonicalise($new), $this->canonicalise($old));
    }

    public function legacyAlgo($data)
    {
        // This is the code taken from the old gen_smokeping script, with echos and sql queries replaced
        $lines = [];
        $lines[] = '' . PHP_EOL;
        $lines[] = 'menu = Top' . PHP_EOL;
        $lines[] = 'title = Network Latency Grapher' . PHP_EOL;
        $lines[] = '' . PHP_EOL;

        foreach ($data as $groupName => $devices) {
            //Dot and space need to be replaced, since smokeping doesn't accept it at this level
            $lines[] = '+ ' . str_replace(['.', ' '], '_', $groupName) . PHP_EOL;
            $lines[] = 'menu = ' . $groupName . PHP_EOL;
            $lines[] = 'title = ' . $groupName . PHP_EOL;
            foreach ($devices as $device) {
                $lines[] = '++ ' . str_replace(['.', ' '], '_', $device) . PHP_EOL;
                $lines[] = 'menu = ' . $device . PHP_EOL;
                $lines[] = 'title = ' . $device . PHP_EOL;
                $lines[] = 'host = ' . $device . PHP_EOL . PHP_EOL;
            }
        }

        // Return a string as we need to evaluate the entire thing as a block
        return implode('', $lines);
    }

    public function canonicalise($input)
    {
        $input = explode(PHP_EOL, $input);

        $output = [];

        foreach ($input as $line) {
            if (trim($line) !== '') {
                $output[] = trim($line);
            }
        }

        return implode(PHP_EOL, $output);
    }
}
