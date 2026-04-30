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
        $this->assertTrue($deviceAttributes['properties']['hostname']['readOnly']);
        $this->assertSame('boolean', $deviceAttributes['properties']['isUp']['type']);
        $this->assertSame('integer', $deviceAttributes['properties']['uptime']['type']);
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
        $this->assertArrayNotHasKey('configuration', $writeAttrs['properties']);

        $body = $response->json();
        $postBody = $body['paths']['/api/v1/alert-transports']['post']['requestBody']['content']['application/vnd.api+json']['schema'];
        $this->assertSame('#/components/schemas/AlertTransportWriteAttributes', $postBody['$ref']);
    }

    public function testNestedRelationshipPathsArePresent(): void
    {
        $response = $this->getJson('/api/v1/openapi.json?fresh=1');

        $paths = array_keys($response->json('paths'));

        foreach (['/api/v1/devices/{id}/ports', '/api/v1/devices/{id}/location', '/api/v1/devices/{id}/groups'] as $expected) {
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

    public function testDocsEndpointRendersSwaggerUi(): void
    {
        $response = $this->get('/api/v1/docs');

        $response->assertOk();
        $response->assertSee('SwaggerUIBundle', false);
        $response->assertSee('openapi.json', false);
    }
}
