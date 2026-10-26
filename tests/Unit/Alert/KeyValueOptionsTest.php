<?php

namespace LibreNMS\Tests\Unit\Alert;

use InvalidArgumentException;
use LibreNMS\Alert\KeyValueOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class KeyValueOptionsTest extends TestCase
{
    public function testRowsPreserveStringValuesAndIgnoreBlankRows(): void
    {
        self::assertSame(['team' => '0', 'details' => "a=b\nsecond line"], KeyValueOptions::fromRows([
            ['name' => ' team ', 'value' => '0'],
            ['name' => '', 'value' => ''],
            ['name' => 'details', 'value' => "a=b\nsecond line"],
        ]));
        self::assertSame([], KeyValueOptions::fromRows([]));
    }

    /** @param array<array-key, mixed> $rows */
    #[DataProvider('invalidRows')]
    public function testInvalidRowsAreRejected(array $rows): void
    {
        $this->expectException(InvalidArgumentException::class);
        KeyValueOptions::fromRows($rows);
    }

    /** @return array<string, array{array<array-key, mixed>}> */
    public static function invalidRows(): array
    {
        return [
            'duplicate' => [[['name' => 'team', 'value' => 'a'], ['name' => ' team ', 'value' => 'b']]],
            'missing name' => [[['name' => '', 'value' => 'a']]],
            'nested value' => [[['name' => 'team', 'value' => []]]],
            'missing value' => [[['name' => 'team']]],
            'scalar row' => [['team']],
        ];
    }

    public function testStoredMapsAndLegacyTextAreReadWithoutTemplateExpansion(): void
    {
        self::assertSame(['team' => '{{ $hostname }}'], KeyValueOptions::fromConfig(['team' => '{{ $hostname }}']));
        self::assertSame(['team' => 'last', 'url' => 'a=b'], KeyValueOptions::fromConfig("team=first\r\nteam=last\nurl=a=b\nignored"));
        self::assertSame([], KeyValueOptions::fromConfig(null));
    }
}
