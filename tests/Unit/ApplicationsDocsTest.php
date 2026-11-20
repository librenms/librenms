<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Tests\TestCase;

class ApplicationsDocsTest extends TestCase
{
    /** @return array<string, string> */
    private function docs(): array
    {
        return json_decode(file_get_contents(base_path('resources/definitions/applications.json')), true) ?? [];
    }

    /** @return list<string> */
    private function types(): array
    {
        return array_map(fn (string $file) => basename($file, '.inc.php'), glob(base_path('includes/polling/applications/*.inc.php')) ?: []);
    }

    public function testDocsMapOnlyReferencesExistingApplicationTypes(): void
    {
        $docs = $this->docs();

        $this->assertNotCount(0, $docs, 'resources/definitions/applications.json must not be empty');

        $types = $this->types();

        foreach ($docs as $type => $url) {
            $this->assertContains($type, $types, "applications.json references unknown application type: $type");
        }
    }

    public function testDocsUrlsUseTheApplicationsDocsBase(): void
    {
        foreach ($this->docs() as $type => $url) {
            $this->assertMatchesRegularExpression('#^https://docs\.librenms\.org/Extensions/Applications/.+/$#', $url, "Invalid docs URL for $type: $url");
        }
    }
}
