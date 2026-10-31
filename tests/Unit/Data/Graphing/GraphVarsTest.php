<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\GraphVars;
use LibreNMS\Tests\TestCase;

class GraphVarsTest extends TestCase
{
    public function test_it_parses_strict_types(): void
    {
        $vars = new GraphVars(['id' => '12', 'list' => '1,2', 'arr' => [1], 'f' => '0.5', 'yes' => 'yes', 'off' => 'off', 'bad' => 'maybe', 'n' => 5]);

        $this->assertSame(12, $vars->int('id'));
        $this->assertNull($vars->int('list'));
        $this->assertNull($vars->int('arr'));
        $this->assertNull($vars->int('missing'));
        $this->assertSame(0.5, $vars->float('f'));
        $this->assertTrue($vars->bool('yes'));
        $this->assertFalse($vars->bool('off'));
        $this->assertNull($vars->bool('bad'));
        $this->assertSame('5', $vars->string('n'));
        $this->assertNull($vars->string('arr'));
    }
}
