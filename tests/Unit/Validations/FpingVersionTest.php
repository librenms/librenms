<?php

namespace LibreNMS\Tests\Unit\Validations;

use LibreNMS\Tests\TestCase;
use LibreNMS\Validations\Programs;
use LibreNMS\Validator;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class FpingVersionTest extends TestCase
{
    #[DataProvider('versions')]
    public function testFpingVersion(string $version, bool $warns): void
    {
        $validator = Mockery::mock(Validator::class);
        if ($warns) {
            $validator->shouldReceive('warn')->once()->withArgs(fn (string $message, string $fix) => str_starts_with($message, "fping $version is older than 4.0 ")
                && $fix === 'Update fping to version 4.0 or newer.');
        } else {
            $validator->shouldNotReceive('warn');
        }

        (new Programs)->checkFpingVersion($validator, $version);
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function versions(): array
    {
        return [
            'unknown' => ['', false],
            '3.16' => ['3.16', true],
            '4.0' => ['4.0', false],
            '5.5' => ['5.5', false],
        ];
    }
}
