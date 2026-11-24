<?php

namespace LibreNMS\Tests\Unit\Validations;

use Illuminate\Support\Facades\File;
use LibreNMS\Tests\TestCase;
use LibreNMS\ValidationResult;
use LibreNMS\Validations\Poller\CheckLegacySensorIncludes;

final class LegacySensorIncludesTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir() . '/librenms-sensor-includes-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    public function testDisabledWithoutIncludes(): void
    {
        $this->assertFalse(new CheckLegacySensorIncludes($this->dir)->enabled());

        File::ensureDirectoryExists("$this->dir/temperature");
        file_put_contents("$this->dir/temperature/README", '');
        $this->assertFalse(new CheckLegacySensorIncludes($this->dir)->enabled());
    }

    public function testWarnsWithTheIncludes(): void
    {
        File::ensureDirectoryExists("$this->dir/temperature");
        File::ensureDirectoryExists("$this->dir/state");
        file_put_contents("$this->dir/temperature/myos.inc.php", '<?php');
        file_put_contents("$this->dir/state/myos.inc.php", '<?php');

        $check = new CheckLegacySensorIncludes($this->dir);
        $this->assertTrue($check->enabled());

        $result = $check->validate();
        $this->assertSame(ValidationResult::WARNING, $result->getStatus());
        $this->assertSame(["$this->dir/state/myos.inc.php", "$this->dir/temperature/myos.inc.php"], $result->getList());
        $this->assertStringContainsString('https://docs.librenms.org/Developing/os/Health-Information/', (string) $result->getFix());
    }
}
