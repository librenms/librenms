<?php

declare(strict_types=1);

namespace LibreNMS\Tests\Feature\Api\V1;

use LibreNMS\Tests\TestCase;

class OpenApiSpecTest extends TestCase
{
    public function testSpecEndpointReturnsValidOpenApiDocument(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $response->assertOk();

        $body = $response->json();

        $this->assertSame('3.0.2', $body['openapi'] ?? null);
        $this->assertSame('LibreNMS API', $body['info']['title'] ?? null);

        $paths = array_keys($body['paths'] ?? []);
        $this->assertContains('/api/v1/health', $paths);
        $this->assertContains('/api/v1/system', $paths);
        $this->assertContains('/api/v1/devices', $paths);
        $this->assertContains('/api/v1/devices/{id}', $paths);

        $schemas = array_keys($body['components']['schemas'] ?? []);
        $this->assertContains('JsonApiPagination', $schemas);
        $this->assertContains('JsonApiList', $schemas);
        $this->assertContains('JsonApiSingle', $schemas);
        $this->assertContains('JsonApiError', $schemas);
        $this->assertContains('DeviceAttributes', $schemas);
        $this->assertContains('DeviceResource', $schemas);
    }

    public function testDeviceAttributesSchemaReflectsRepositoryFields(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $deviceAttributes = $response->json('components.schemas.DeviceAttributes');

        $this->assertSame('object', $deviceAttributes['type'] ?? null);
        $this->assertArrayHasKey('hostname', $deviceAttributes['properties']);
        $this->assertSame('string', $deviceAttributes['properties']['hostname']['type']);
        $this->assertArrayNotHasKey('readOnly', $deviceAttributes['properties']['hostname']);
        $this->assertSame('boolean', $deviceAttributes['properties']['isUp']['type']);
        $this->assertTrue($deviceAttributes['properties']['isUp']['readOnly']);
        $this->assertSame('integer', $deviceAttributes['properties']['uptime']['type']);
        $this->assertTrue($deviceAttributes['properties']['os']['readOnly']);
    }

    public function testEnumValuesPropagateFromValidationRules(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $write = $response->json('components.schemas.AlertRuleWriteAttributes.properties.severity');
        $this->assertSame('string', $write['type']);
        $this->assertSame(['ok', 'warning', 'critical'], $write['enum']);

        $read = $response->json('components.schemas.AlertRuleAttributes.properties.severity');
        $this->assertSame(['ok', 'warning', 'critical'], $read['enum']);
    }

    public function testAlertsAreReadOnly(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $methods = array_keys($response->json('paths./api/v1/alerts'));
        $itemMethods = array_keys($response->json('paths./api/v1/alerts/{id}'));

        $this->assertSame(['get'], $methods);
        $this->assertSame(['get'], $itemMethods);
    }

    public function testAlertTransportsExposeFullCrudWithWriteSchema(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $methods = array_keys($response->json('paths./api/v1/alert-transports'));
        $itemMethods = array_keys($response->json('paths./api/v1/alert-transports/{id}'));

        $this->assertContains('post', $methods);
        $this->assertContains('patch', $itemMethods);
        $this->assertContains('delete', $itemMethods);

        $writeAttrs = $response->json('components.schemas.AlertTransportWriteAttributes');
        $this->assertSame('object', $writeAttrs['type']);
        $this->assertArrayHasKey('name', $writeAttrs['properties']);
        $this->assertArrayHasKey('isDefault', $writeAttrs['properties']);
        $this->assertArrayHasKey('configuration', $writeAttrs['properties']);

        $body = $response->json();
        $postBody = $body['paths']['/api/v1/alert-transports']['post']['requestBody']['content']['application/vnd.api+json']['schema'];
        $this->assertSame('#/components/schemas/AlertTransportWriteAttributes', $postBody['$ref']);
    }

    public function testNestedRelationshipPathsArePresent(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $paths = array_keys($response->json('paths'));

        foreach (['/api/v1/devices/{id}/ports', '/api/v1/devices/{id}/location', '/api/v1/devices/{id}/device-groups'] as $expected) {
            $this->assertContains($expected, $paths);
        }
    }

    public function testDeviceResourceRelationshipsBlock(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $rel = $response->json('components.schemas.DeviceResource.properties.relationships.properties');
        $this->assertIsArray($rel);
        $this->assertArrayHasKey('ports', $rel);
        $this->assertArrayHasKey('location', $rel);

        $this->assertSame('array', $rel['ports']['properties']['data']['type']);
        $this->assertSame(['ports'], $rel['ports']['properties']['data']['items']['properties']['type']['enum']);

        $this->assertSame('object', $rel['location']['properties']['data']['type']);
        $this->assertTrue($rel['location']['properties']['data']['nullable']);
        $this->assertSame(['locations'], $rel['location']['properties']['data']['properties']['type']['enum']);
    }

    public function testIncludeEnumPopulatedFromRelated(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $params = $response->json('paths./api/v1/devices.get.parameters');
        $include = null;
        foreach ($params as $p) {
            if ($p['name'] === 'include') {
                $include = $p;
                break;
            }
        }

        $this->assertNotNull($include);
        $this->assertStringContainsString('ports', $include['description']);
        $this->assertStringContainsString('location', $include['description']);
        $this->assertNotEmpty($include['schema']['pattern'] ?? null);
    }

    public function testDevicesListEndpointAdvertisesQueryParameters(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $params = $response->json('paths./api/v1/devices.get.parameters');
        $this->assertNotEmpty($params);

        $byName = [];
        foreach ($params as $p) {
            $byName[$p['name']] = $p;
        }

        foreach (['page', 'perPage', 'include', 'fields[devices]', 'sort', 'search', 'hostname', 'isUp', 'uptime'] as $expected) {
            $this->assertArrayHasKey($expected, $byName, "Expected param {$expected} on /api/v1/devices");
        }

        $this->assertSame('integer', $byName['page']['schema']['type']);
        $this->assertSame('boolean', $byName['isUp']['schema']['type']);
        $this->assertSame('integer', $byName['uptime']['schema']['type']);
        $this->assertStringContainsString('hostname', $byName['search']['description']);
    }

    public function testAttachableRelationshipsExposeAttachSyncDetachPaths(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $paths = array_keys($response->json('paths'));

        foreach (['attach', 'sync', 'detach'] as $action) {
            $this->assertContains("/api/v1/device-groups/{id}/{$action}/devices", $paths);
        }
    }

    public function testHasManyRelationshipsDoNotExposeAttachPaths(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $paths = array_keys($response->json('paths'));

        $this->assertContains('/api/v1/devices/{id}/ports', $paths);
        $this->assertNotContains('/api/v1/devices/{id}/attach/ports', $paths);
        $this->assertNotContains('/api/v1/devices/{id}/sync/ports', $paths);
        $this->assertNotContains('/api/v1/devices/{id}/detach/ports', $paths);
    }

    public function testAttachOperationDescribesBodyShape(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $attach = $response->json('paths./api/v1/device-groups/{id}/attach/devices.post');
        $this->assertSame('Attach devices to a device-group', $attach['summary']);

        $body = $attach['requestBody']['content']['application/vnd.api+json']['schema'];
        $this->assertArrayHasKey('devices', $body['properties']);
        $this->assertSame('array', $body['properties']['devices']['type']);
        $this->assertSame('integer', $body['properties']['devices']['items']['type']);
        $this->assertSame(['devices'], $body['required']);

        $this->assertArrayHasKey('201', $attach['responses']);

        $detach = $response->json('paths./api/v1/device-groups/{id}/detach/devices.post');
        $this->assertArrayHasKey('204', $detach['responses']);
    }

    public function testAttachUrlUsesFieldAttributeNotArrayKey(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $paths = array_keys($response->json('paths'));

        // UserRepository's relations now use kebab-case URLs end-to-end. The
        // RestifyAttachRelationResolver middleware bridges the URL slug to the
        // model's camelCase relation method (e.g. devicesOwned()).
        $this->assertContains('/api/v1/users/{id}/attach/devices-owned', $paths);
        $this->assertContains('/api/v1/users/{id}/attach/ports-owned', $paths);
        $this->assertNotContains('/api/v1/users/{id}/attach/devicesOwned', $paths);
    }

    public function testNewlyAddedAttachableRelationsAreInSpec(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $paths = array_keys($response->json('paths'));

        foreach ([
            '/api/v1/alert-rules/{id}/attach/templates',
            '/api/v1/devices/{id}/attach/parents',
            '/api/v1/device-groups/{id}/attach/users',
            '/api/v1/users/{id}/attach/bills',
            '/api/v1/users/{id}/attach/device-groups',
            '/api/v1/alert-transport-groups/{id}/attach/transports',
            '/api/v1/alert-transport-groups',
        ] as $expected) {
            $this->assertContains($expected, $paths, "Expected path {$expected}");
        }
    }

    public function testActionPathsAreInSpec(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $paths = array_keys($response->json('paths'));

        foreach ([
            '/api/v1/devices/{id}/actions',
            '/api/v1/device-groups/{id}/actions',
            '/api/v1/locations/{id}/actions',
            '/api/v1/alerts/{id}/actions',
        ] as $expected) {
            $this->assertContains($expected, $paths, "Expected actions path {$expected}");
        }
    }

    public function testActionPostOperationDocumentsActionEnumAndPayload(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $perform = $response->json('paths./api/v1/devices/{id}/actions.post');
        $this->assertNotNull($perform);

        $byName = [];
        foreach ($perform['parameters'] as $p) {
            $byName[$p['name']] = $p;
        }
        $this->assertArrayHasKey('action', $byName);
        $this->assertTrue($byName['action']['required']);
        $this->assertContains('discover', $byName['action']['schema']['enum']);
        $this->assertContains('maintenance', $byName['action']['schema']['enum']);

        $body = $perform['requestBody']['content']['application/vnd.api+json']['schema'];
        $this->assertArrayHasKey('oneOf', $body);
    }

    public function testActionListEndpointDescribesAvailableActions(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $list = $response->json('paths./api/v1/devices/{id}/actions.get');
        $this->assertNotNull($list);
        $this->assertSame('List available actions on Devices', $list['summary']);
    }

    public function testRepositoriesWithoutActionsHaveNoActionsPath(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $paths = array_keys($response->json('paths'));

        $this->assertNotContains('/api/v1/ports/{id}/actions', $paths);
        $this->assertNotContains('/api/v1/bills/{id}/actions', $paths);
    }

    public function testDocsEndpointRendersSwaggerUi(): void
    {
        $response = $this->get('/api/v1/docs');

        $response->assertOk();
        $response->assertSee('SwaggerUIBundle', false);
        $response->assertSee('openapi.json', false);
    }
}
