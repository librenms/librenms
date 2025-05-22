<?php

namespace LibreNMS\Tests\Unit;

use App\Models\Device;
use App\Models\DeviceTagKey;
use LibreNMS\Tests\InMemoryDbTestCase;

class DeviceTagTest extends InMemoryDbTestCase
{
    public function test_set_and_get_tags_various_types()
    {
        $device = Device::factory()->create();

        // String tag (default)
        $device->setTag(['owner' => 'alice']);
        $this->assertEquals(['owner' => 'alice'], $device->getTag('owner')->toArray());
        $this->assertEquals(['owner' => 'alice'], $device->getTag());

        // Multiple tags
        $tags = ['owner' => 'bob', 'location' => 'datacenter'];
        $device->setTag($tags);
        $this->assertEquals(['owner' => 'bob'], $device->getTag('owner')->toArray());
        $this->assertEquals(['location' => 'datacenter'], $device->getTag('location')->toArray());
        $this->assertEquals($tags, $device->getTag());

        // Update tag value
        $device->setTag(['role' => 'router']);
        $device->setTag(['role' => 'switch']);
        $this->assertEquals(['role' => 'switch'], $device->getTag('role')->toArray());

        // Set tag with email type
        $d = DeviceTagKey::create(['key' => 'email_tag', 'type' => 'email', 'visible' => true]);
        $device->setTag(['email_tag' => 'user@example.com']);
        $this->assertEquals(['email_tag' => 'user@example.com'], $device->getTag('email_tag')->toArray());
        $this->assertEquals($d->type, 'email');

        // Set tag with integer type
        $d = DeviceTagKey::create(['key' => 'int_tag', 'type' => 'integer', 'visible' => true]);
        $device->setTag(['int_tag' => 123]);
        $this->assertEquals(['int_tag' => 123], $device->getTag('int_tag')->toArray());
        $this->assertEquals($d->type, 'integer');

        // Set tag with url type
        $d = DeviceTagKey::create(['key' => 'url_tag', 'type' => 'url', 'visible' => true]);
        $device->setTag(['url_tag' => 'https://example.com']);
        $this->assertEquals(['url_tag' => 'https://example.com'], $device->getTag('url_tag')->toArray());
        $this->assertEquals($d->type, 'url');

        // Set tag with timestamp type
        $d = DeviceTagKey::create(['key' => 'timestamp_tag', 'type' => 'timestamp', 'visible' => true]);
        $device->setTag(['timestamp_tag' => 123456789]);
        $this->assertEquals(['timestamp_tag' => 123456789], $device->getTag('timestamp_tag')->toArray());
        $this->assertEquals($d->type, 'timestamp');
    }

    public function test_set_tag_invalid_types_throw_exception()
    {
        $device = Device::factory()->create();
        DeviceTagKey::create(['key' => 'email_tag', 'type' => 'email', 'visible' => true]);
        DeviceTagKey::create(['key' => 'int_tag', 'type' => 'integer', 'visible' => true]);
        DeviceTagKey::create(['key' => 'url_tag', 'type' => 'url', 'visible' => true]);
        DeviceTagKey::create(['key' => 'timestamp_tag', 'type' => 'timestamp', 'visible' => true]);

        // Email type
        try {
            $device->setTag(['email_tag' => 'not-an-email']);
            $this->fail('Expected exception for invalid email');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('email_tag', $e->getMessage());
        }

        // Integer type
        try {
            $device->setTag(['int_tag' => 'not-an-int']);
            $this->fail('Expected exception for invalid integer');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('int_tag', $e->getMessage());
        }

        // URL type
        try {
            $device->setTag(['url_tag' => 'not-a-url']);
            $this->fail('Expected exception for invalid url');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('url_tag', $e->getMessage());
        }

        // Timestamp type
        try {
            $device->setTag(['timestamp_tag' => 'not-a-timestamp']);
            $this->fail('Expected exception for invalid timestamp');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('timestamp_tag', $e->getMessage());
        }
    }

    public function test_delete_tag_and_invalid_key_handling()
    {
        $device = Device::factory()->create();
        $device->setTag(['foo' => 'bar', 'baz' => 'qux']);
        $device->deleteTag('foo');
        $this->assertEquals([], $device->getTag('foo'));
        $this->assertEquals(['baz' => 'qux'], $device->getTag());

        // Deleting a non-existent key should be a noop
        $this->assertEmpty($device->deleteTag('nonexistent'));
    }

    public function test_get_tag_returns_empty_array_for_missing_key()
    {
        $device = Device::factory()->create();
        $this->assertEquals([], $device->getTag('not_set'));
    }
}
