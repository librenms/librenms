<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\OS\Routeros;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RouterosLteStateTest extends TestCase
{
    #[DataProvider('states')]
    public function testNormalizeLteState(string $column, string $value, int $expected): void
    {
        $this->assertSame($expected, Routeros::normalizeLteState($column, $value));
    }

    public function testLteEntityIndexFitsTheInventoryColumn(): void
    {
        $this->assertSame(1000000030, Routeros::lteEntityIndex(3));
        $this->assertSame(1000000031, Routeros::lteEntityIndex('3', true));
        $this->assertSame(2147483641, Routeros::lteEntityIndex(114748364, true), 'largest ifIndex that fits');
        $this->assertNull(Routeros::lteEntityIndex(114748365));
        $this->assertNull(Routeros::lteEntityIndex(200000000));
        $this->assertNull(Routeros::lteEntityIndex(200000000, true));
    }

    #[DataProvider('cellIds')]
    public function testValidCellId(mixed $value, ?int $expected): void
    {
        $this->assertSame($expected, Routeros::validCellId($value));
    }

    /**
     * @return array<string, array{mixed, int|null}>
     */
    public static function cellIds(): array
    {
        return [
            'cell id' => ['81592885', 81592885],
            'not reported' => ['-1', null],
            'all ones' => ['4294967295', null],
            'empty' => ['', null],
            'missing' => [null, null],
        ];
    }

    /**
     * @return array<string, array{string, string, int}>
     */
    public static function states(): array
    {
        return [
            'rat lte (ros 7)' => ['mtxrLTEModemRAT', 'Evolved 3G (LTE)', 4],
            'rat eutran' => ['mtxrLTEModemRAT', 'EUTRAN', 4],
            'rat e-utran' => ['mtxrLTEModemRAT', 'E-UTRAN', 4],
            'rat utran' => ['mtxrLTEModemRAT', 'UTRAN', 3],
            'rat wcdma' => ['mtxrLTEModemRAT', 'WCDMA', 3],
            'rat gsm' => ['mtxrLTEModemRAT', 'GSM', 2],
            'rat 5g sa' => ['mtxrLTEModemRAT', '5G NR', 6],
            'rat nr sa' => ['mtxrLTEModemRAT', 'NR SA', 6],
            'rat 5g nsa' => ['mtxrLTEModemRAT', 'LTE + NR', 5],
            'rat nr nsa' => ['mtxrLTEModemRAT', 'NR NSA', 5],
            'rat unknown' => ['mtxrLTEModemRAT', 'unknown', 0],
            'sim present' => ['mtxrLTEModemUICC', '8931000000000000000', 1],
            'sim missing' => ['mtxrLTEModemUICC', '', 0],
            'registered' => ['mtxrLTEModemRegStatus', 'registered', 1],
            'roaming' => ['mtxrLTEModemRegStatus', 'roaming', 2],
            'searching' => ['mtxrLTEModemRegStatus', 'not registered, searching', 3],
            'not registered' => ['mtxrLTEModemRegStatus', 'not registered', 5],
            'pin ok' => ['mtxrLTEModemPinStatus', 'ok', 1],
            'pin required' => ['mtxrLTEModemPinStatus', 'PIN required', 2],
            'puk required' => ['mtxrLTEModemPinStatus', 'PUK required', 3],
            'no sim' => ['mtxrLTEModemPinStatus', 'SIM not inserted', 0],
        ];
    }
}
