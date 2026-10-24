<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Url;

final class UrlTest extends TestCase
{
    public function testGenerate(): void
    {
        $this->assertSame(url('device') . '/device=1/tab=loadbalancer/', Url::generate(['page' => 'device', 'device' => 1], ['tab' => 'loadbalancer']));
        $this->assertSame(url('plugin/v1') . '/', Url::generate(['page' => 'plugin/v1', 'opt' => 'x']));
    }

    public function testGenerateEncodesPage(): void
    {
        $url = Url::generate(['page' => "x';alert(1);//\"><img>", 'device' => 1]);

        $this->assertStringNotContainsString("'", $url);
        $this->assertStringNotContainsString('"', $url);
        $this->assertStringNotContainsString('<', $url);
        $this->assertSame(url('x%27%3Balert%281%29%3B//%22%3E%3Cimg%3E') . '/device=1/', $url);
    }
}
