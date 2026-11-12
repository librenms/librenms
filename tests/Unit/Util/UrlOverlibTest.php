<?php

namespace LibreNMS\Tests\Unit\Util;

use App\Facades\LibrenmsConfig;
use DOMDocument;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Url;

final class UrlOverlibTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('web_mouseover', true);
    }

    /**
     * Return the onmouseover attribute as the browser would see it (entities decoded)
     */
    private function mouseover(string $html): string
    {
        $dom = new DOMDocument;
        $dom->loadHTML('<html><body>' . $html . '</body></html>', LIBXML_NOERROR);

        return $dom->getElementsByTagName('a')->item(0)->getAttribute('onmouseover');
    }

    public function testOverlibContentEscapesText(): void
    {
        $content = Url::overlibContent(['type' => 'storage_usage', 'id' => 1], "host - C:\\ ');alert(1);// <img src=x onerror=alert(2)>");
        $script = $this->mouseover(Url::overlibLink('#', 'text', $content));

        $this->assertStringStartsWith("return overlib('", $script);
        $this->assertStringNotContainsString("');alert(1)", $script);
        $this->assertStringNotContainsString('<img src=x', $script);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(2)&gt;', $script);
        $this->assertStringContainsString('C:\\\\ ', $script);
    }

    public function testOverlibLinkKeepsEncodedEntitiesAsText(): void
    {
        $script = $this->mouseover(Url::overlibLink('#', 'text', 'name &#039;);alert(1);// &lt;img src=x onerror=alert(2)&gt;'));

        $this->assertStringNotContainsString("');alert(1)", $script);
        $this->assertStringNotContainsString('<img src=x', $script);
        $this->assertStringContainsString('&#039;);alert(1);// &lt;img src=x onerror=alert(2)&gt;', $script);
    }

    public function testOverlibLinkPreservesMarkup(): void
    {
        $script = $this->mouseover(Url::overlibLink('/graphs', 'text', "<div class=\\'test\\'><img src=\\'/graph.php?a=1&b=2\\' /></div>"));

        $this->assertStringContainsString("<div class=\\'test\\'><img src=\\'/graph.php?a=1&b=2\\' /></div>", $script);
    }
}
