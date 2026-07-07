<?php

namespace LibreNMS\Tests;

use LibreNMS\CollectdColor;

require_once __DIR__ . '/../includes/html/collectd/CollectdColor.php';

class CollectdColorTest extends TestCase
{
    public function testFadeAcceptsNamespacedBackground(): void
    {
        $background = new CollectdColor('2e3338');
        $area = new CollectdColor('3e444c');
        $area->fade($background);

        $this->assertSame('32373c', $area->toString());
    }

    public function testFadeDefaultsToWhiteWithoutBackground(): void
    {
        $area = new CollectdColor('3e444c');
        $area->fade();

        $this->assertSame('ced0d2', $area->toString());
    }
}
