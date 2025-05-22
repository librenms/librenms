<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Models\Device;
use App\Models\User;
use LibreNMS\Tests\InMemoryDbTestCase;

class TestDeviceTagApi extends InMemoryDbTestCase
{
    protected function actingAsAdmin()
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);
    }

    public function test_set_and_get_tags_various_types()
    {
        $device = Device::factory()->create(['hostname' => 'apitest1']);
        $this->actingAsAdmin();

        // Set and get multiple tags
        $response = $this->postJson("/api/v0/devices/{$device->hostname}/tags", [
            'tags' => [
                'apiowner' => 'bob',
                'apilocation' => 'rack1',
                'hiddenapi' => 'secret',
            ],
            'visible' => true,
        ]);
        $response->assertStatus(200)
            ->assertJsonFragment(['apiowner' => 'bob', 'apilocation' => 'rack1']);

        // Set hidden tag
        $response = $this->postJson("/api/v0/devices/{$device->hostname}/tags", [
            'tags' => ['hiddenapi' => 'secret'],
            'visible' => false,
        ]);
        $response->assertStatus(200)
            ->assertJsonFragment(['hiddenapi' => 'secret']);
        $tagKey = \App\Models\DeviceTagKey::where('key', 'hiddenapi')->first();
        $this->assertNotNull($tagKey);
        $this->assertFalse($tagKey->visible);

        // Get all tags
        $response = $this->getJson("/api/v0/devices/{$device->hostname}/tags");
        $response->assertStatus(200)
            ->assertJsonFragment(['apiowner' => 'bob', 'apilocation' => 'rack1', 'hiddenapi' => 'secret']);

        // Get single tag
        $response = $this->getJson("/api/v0/devices/{$device->hostname}/tags/apiowner");
        $response->assertStatus(200)
            ->assertJsonFragment(['apiowner' => 'bob']);

        // Delete tag
        $response = $this->deleteJson("/api/v0/devices/{$device->hostname}/tags/apiowner");
        $response->assertStatus(200);
        $response = $this->getJson("/api/v0/devices/{$device->hostname}/tags");
        $response->assertStatus(200)
            ->assertJsonMissing(['apiowner' => 'bob']);

        // Set and get typed tags
        \App\Models\DeviceTagKey::create(['key' => 'email_tag', 'type' => 'email', 'visible' => true]);
        $response = $this->postJson("/api/v0/devices/{$device->hostname}/tags", [
            'tags' => ['email_tag' => 'user@example.com'],
        ]);
        $response->assertStatus(200)
            ->assertJsonFragment(['email_tag' => 'user@example.com']);
        \App\Models\DeviceTagKey::create(['key' => 'int_tag', 'type' => 'integer', 'visible' => true]);
        $response = $this->postJson("/api/v0/devices/{$device->hostname}/tags", [
            'tags' => ['int_tag' => 123],
        ]);
        $response->assertStatus(200)
            ->assertJsonFragment(['int_tag' => 123]);
        \App\Models\DeviceTagKey::create(['key' => 'url_tag', 'type' => 'url', 'visible' => true]);
        $response = $this->postJson("/api/v0/devices/{$device->hostname}/tags", [
            'tags' => ['url_tag' => 'https://example.com'],
        ]);
        $response->assertStatus(200)
            ->assertJsonFragment(['url_tag' => 'https://example.com']);
    }

    public function test_set_tag_invalid_types_return_422()
    {
        $device = Device::factory()->create(['hostname' => 'apitest2']);
        $this->actingAsAdmin();
        \App\Models\DeviceTagKey::create(['key' => 'email_tag', 'type' => 'email', 'visible' => true]);
        \App\Models\DeviceTagKey::create(['key' => 'int_tag', 'type' => 'integer', 'visible' => true]);
        \App\Models\DeviceTagKey::create(['key' => 'url_tag', 'type' => 'url', 'visible' => true]);
        \App\Models\DeviceTagKey::create(['key' => 'timestamp_tag', 'type' => 'timestamp', 'visible' => true]);

        $response = $this->postJson("/api/v0/devices/{$device->hostname}/tags", [
            'tags' => ['email_tag' => 'not-an-email'],
        ]);
        $response->assertStatus(422);
        $response = $this->postJson("/api/v0/devices/{$device->hostname}/tags", [
            'tags' => ['int_tag' => 'not-an-int'],
        ]);
        $response->assertStatus(422);
        $response = $this->postJson("/api/v0/devices/{$device->hostname}/tags", [
            'tags' => ['url_tag' => 'not-a-url'],
        ]);
        $response->assertStatus(422);
        $response = $this->postJson("/api/v0/devices/{$device->hostname}/tags", [
            'tags' => ['timestamp_tag' => 'not-a-timestamp'],
        ]);
        $response->assertStatus(422);
    }

    public function test_api_define_tag_types_and_visibility()
    {
        $this->actingAsAdmin();
        // Define a visible string tag
        $response = $this->postJson('/api/v0/tags/define', [
            'tags' => ['mytag'],
            'type' => 'string',
        ]);
        $response->assertStatus(200);
        $tagKey = \App\Models\DeviceTagKey::where('key', 'mytag')->first();
        $this->assertNotNull($tagKey);
        $this->assertEquals('string', $tagKey->type);
        $this->assertTrue($tagKey->visible);

        // Define a hidden integer tag
        $response = $this->postJson('/api/v0/tags/define', [
            'tags' => ['hiddenint'],
            'type' => 'integer',
            'visible' => false,
        ]);
        $response->assertStatus(200);
        $tagKey = \App\Models\DeviceTagKey::where('key', 'hiddenint')->first();
        $this->assertNotNull($tagKey);
        $this->assertEquals('integer', $tagKey->type);
        $this->assertFalse($tagKey->visible);

        // Define multiple tags at once
        $response = $this->postJson('/api/v0/tags/define', [
            'tags' => ['tag1', 'tag2'],
            'type' => 'email',
        ]);
        $response->assertStatus(200);
        $tag1 = \App\Models\DeviceTagKey::where('key', 'tag1')->first();
        $tag2 = \App\Models\DeviceTagKey::where('key', 'tag2')->first();
        $this->assertNotNull($tag1);
        $this->assertNotNull($tag2);
        $this->assertEquals('email', $tag1->type);
        $this->assertEquals('email', $tag2->type);
    }
}
