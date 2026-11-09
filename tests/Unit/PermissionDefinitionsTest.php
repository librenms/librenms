<?php

namespace LibreNMS\Tests\Unit;

use LibreNMS\Tests\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Keeps resources/definitions/permissions.yaml in sync with the policies and lang/en/permissions.php
 */
final class PermissionDefinitionsTest extends TestCase
{
    /**
     * Checked by a policy, but nothing calls the ability, so they are not offered
     */
    private const NOT_GRANTABLE = [
        'inventory.delete',
        'inventory.update',
        'port.forceDelete',
        'port.restore',
    ];

    /** @var array{groups: array<string, string[]>, hidden: string[], risky: string[]} */
    private array $definitions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->definitions = Yaml::parseFile(resource_path('definitions/permissions.yaml'));
    }

    public function testGroupsOnlyContainTheirOwnPermissions(): void
    {
        foreach ($this->definitions['groups'] as $group => $permissions) {
            foreach ($permissions as $permission) {
                $this->assertStringStartsWith("$group.", $permission);
            }
        }
    }

    public function testEveryPermissionHasLabelAndDescription(): void
    {
        foreach ($this->allPermissions() as $permission) {
            [$group, $action] = explode('.', $permission, 2);

            $this->assertNotSame("permissions.$group.title", __("permissions.$group.title"), "$group has no title");
            $this->assertNotSame("permissions.$permission.label", __("permissions.$permission.label"), "$permission has no label");
            $this->assertNotSame("permissions.$permission.description", __("permissions.$permission.description"), "$permission has no description");
        }
    }

    public function testRiskyPermissionsExistAndExplainWhy(): void
    {
        $this->assertNotEmpty($this->definitions['risky']);
        $this->assertNotSame('permissions.rbac.risky', __('permissions.rbac.risky'));

        foreach ($this->definitions['risky'] as $permission) {
            $this->assertContains($permission, $this->allPermissions(), "$permission is not defined");
            $this->assertNotSame("permissions.$permission.risk", __("permissions.$permission.risk"), "$permission has no risk text");
        }
    }

    public function testOnlyRiskyPermissionsHaveRiskText(): void
    {
        foreach (__('permissions') as $group => $translations) {
            foreach ($translations as $action => $translation) {
                if (is_array($translation) && isset($translation['risk'])) {
                    $this->assertContains("$group.$action", $this->definitions['risky'], "$group.$action has risk text, but is not listed as risky");
                }
            }
        }
    }

    public function testEveryPolicyPermissionIsDefined(): void
    {
        $defined = $this->allPermissions();

        foreach (glob(app_path('Policies/*Policy.php')) as $file) {
            $source = file_get_contents($file);
            $prefix = preg_match("/globalPrefix = '([^']+)'/", $source, $matches)
                ? $matches[1]
                : strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', basename($file, 'Policy.php')));

            preg_match_all("/hasGlobalPermission\(\\\$user, '([^']+)'/", $source, $matches);
            foreach (array_unique($matches[1]) as $action) {
                $permission = "$prefix.$action";
                if (! in_array($permission, self::NOT_GRANTABLE)) {
                    $this->assertContains($permission, $defined, basename($file) . " checks $permission");
                }
            }
        }
    }

    /**
     * @return string[]
     */
    private function allPermissions(): array
    {
        return [...array_merge(...array_values($this->definitions['groups'])), ...$this->definitions['hidden']];
    }
}
