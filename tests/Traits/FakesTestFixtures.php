<?php

namespace LibreNMS\Tests\Traits;

use Illuminate\Support\Facades\File;

/**
 * Point the application at a temporary base path holding only the given test fixtures
 */
trait FakesTestFixtures
{
    /**
     * @param  array<int, string>  $snmprec  fixture names in tests/snmpsim
     * @param  array<int, string>  $json  fixture names in tests/data
     * @param  array<int, string>  $osDefinitions  OS names in resources/definitions/os_detection
     */
    private function fakeTestFixtures(array $snmprec = [], array $json = [], array $osDefinitions = []): void
    {
        $basePath = sys_get_temp_dir() . '/librenms-fixtures-' . bin2hex(random_bytes(6));
        File::ensureDirectoryExists("$basePath/tests/snmpsim");
        File::ensureDirectoryExists("$basePath/tests/data");
        File::ensureDirectoryExists("$basePath/resources/definitions/os_detection");
        symlink(base_path('lang'), "$basePath/lang");

        foreach ($snmprec as $name) {
            file_put_contents("$basePath/tests/snmpsim/$name.snmprec", "1.3.6.1.2.1.1.1.0|4|Linksys WRT54G\n");
        }
        foreach ($json as $name) {
            file_put_contents("$basePath/tests/data/$name.json", json_encode(['os' => ['discovery' => []]]));
        }
        foreach ($osDefinitions as $os) {
            file_put_contents("$basePath/resources/definitions/os_detection/$os.yaml", "os: $os\n");
        }

        $originalBasePath = base_path();
        $this->app->setBasePath($basePath);

        $this->beforeApplicationDestroyed(function () use ($basePath, $originalBasePath): void {
            $this->app->setBasePath($originalBasePath);
            File::deleteDirectory($basePath);
        });
    }
}
