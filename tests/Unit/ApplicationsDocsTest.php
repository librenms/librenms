<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Applications\ApplicationDefinition;
use LibreNMS\Tests\TestCase;

class ApplicationsDocsTest extends TestCase
{
    /** @return list<string> */
    private function types(): array
    {
        return array_map(fn (string $file) => basename($file, '.inc.php'), glob(base_path('includes/polling/applications/*.inc.php')) ?: []);
    }

    public function testDocUrlsUseTheApplicationsDocsBase(): void
    {
        $withDocs = 0;

        foreach ($this->types() as $type) {
            $url = ApplicationDefinition::forType($type)->docsUrl();

            if ($url === null) {
                continue;
            }

            $withDocs++;
            $this->assertMatchesRegularExpression('#^https://docs\.librenms\.org/Extensions/Applications/.+/$#', $url, "Invalid docs URL for $type: $url");
        }

        $this->assertGreaterThanOrEqual(80, $withDocs, 'Expected the application docs coverage to remain substantial');
    }
}
