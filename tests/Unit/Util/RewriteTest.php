<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Rewrite;

final class RewriteTest extends TestCase
{
    public function testBgpErrorCode(): void
    {
        $this->assertSame('Cease - Administrative Shutdown', Rewrite::bgpErrorCode(6, 2));
        $this->assertSame('Cease - Administrative Shutdown', Rewrite::bgpErrorCode('6', '2'));
        $this->assertSame('Hold Timer Expired', Rewrite::bgpErrorCode(4, 0));
        $this->assertSame('Hold Timer Expired', Rewrite::bgpErrorCode(4, 99));
        $this->assertSame('Unknown', Rewrite::bgpErrorCode(99, 0));
        $this->assertSame('Unknown', Rewrite::bgpErrorCode('', ''));
    }
}
