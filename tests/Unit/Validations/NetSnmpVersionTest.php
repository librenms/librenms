<?php

namespace LibreNMS\Tests\Unit\Validations;

use LibreNMS\Tests\TestCase;
use LibreNMS\Validations\Snmp;
use LibreNMS\Validator;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class NetSnmpVersionTest extends TestCase
{
    #[DataProvider('versions')]
    public function testNetSnmpVersion(string $version, bool $warns): void
    {
        $validator = Mockery::mock(Validator::class);
        if ($warns) {
            $validator->shouldReceive('warn')->once()->withArgs(fn (string $message, string $fix) => str_starts_with($message, "net-snmp $version is older than 5.8.")
                && $fix === 'Update net-snmp to version 5.8 or newer.');
        } else {
            $validator->shouldNotReceive('warn');
        }

        (new Snmp)->checkNetSnmpVersion($validator, $version);
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function versions(): array
    {
        return [
            'not installed' => ['', false],
            '5.7.3' => ['5.7.3', true],
            '5.7.2.1' => ['5.7.2.1', true],
            '5.8' => ['5.8', false],
            '5.9.5.2' => ['5.9.5.2', false],
        ];
    }
}
