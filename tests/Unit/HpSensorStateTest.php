<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Tests\TestCase;

class HpSensorStateTest extends TestCase
{
    /**
     * @return array<int, array{0: string, 1: string, 2: string, 3: string, 4: array<int, array{value: int, generic: int, descr: string}>}>
     */
    private function loadTables(): array
    {
        $file = base_path('includes/discovery/sensors/state/hp.inc.php');
        $source = file_get_contents($file);
        $this->assertNotFalse($source, "Could not read $file");

        // Extract just the $tables = [ ... ]; array literal via the real
        // tokenizer (not a hand-copied literal) -- the rest of the file
        // walks real SNMP data against a live $device, which this test
        // has no need to set up.
        $tokens = token_get_all("<?php\n" . $source);

        $start = null;
        $depth = 0;
        $end = null;
        foreach ($tokens as $i => $token) {
            if ($start === null) {
                if (is_array($token) && $token[0] === T_VARIABLE && $token[1] === '$tables') {
                    $start = $i;
                }

                continue;
            }

            $text = is_array($token) ? $token[1] : $token;
            if ($text === '[') {
                $depth++;
            } elseif ($text === ']') {
                $depth--;
            } elseif ($text === ';' && $depth === 0) {
                $end = $i;
                break;
            }
        }

        $this->assertNotNull($start, 'Could not find $tables declaration in hp.inc.php');
        $this->assertNotNull($end, 'Could not find end of $tables declaration in hp.inc.php');

        $statement = '';
        for ($i = $start; $i <= $end; $i++) {
            $statement .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'hp_tables_');
        file_put_contents($tmpFile, "<?php\n$statement\nreturn \$tables;\n");

        try {
            $tables = include $tmpFile;
        } finally {
            unlink($tmpFile);
        }

        $this->assertIsArray($tables);

        return $tables;
    }

    public function testCpqDaPhyDrvStatusHotSpareMapsToOkGeneric(): void
    {
        $tables = $this->loadTables();

        $cpqDaPhyDrvStatus = null;
        foreach ($tables as $table) {
            if ($table[0] === 'cpqDaPhyDrvStatus') {
                $cpqDaPhyDrvStatus = $table[4];
                break;
            }
        }
        $this->assertNotNull($cpqDaPhyDrvStatus, 'cpqDaPhyDrvStatus table not found in hp.inc.php');

        $hotSpare = null;
        foreach ($cpqDaPhyDrvStatus as $state) {
            if ($state['value'] === 10) {
                $hotSpare = $state;
                break;
            }
        }

        $this->assertNotNull($hotSpare, 'value 10 (hotSpare) not found in cpqDaPhyDrvStatus mapping');
        $this->assertSame('hotSpare', $hotSpare['descr']);
        $this->assertSame(0, $hotSpare['generic']);
    }
}
