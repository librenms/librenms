<?php

namespace LibreNMS\Tests\Feature\Commands;

use App\Models\Device;
use LibreNMS\Tests\InMemoryDbTestCase;

class TestDeviceTagCommand extends InMemoryDbTestCase
{
    public function test_cli_set_and_get_tags_various_types()
    {
        $device = Device::factory()->create();

        // Set and get string tag
        $this->artisan('device:tags', [
            'action' => 'set',
            'device_id' => $device->device_id,
            'tags' => ['cliowner=carol'],
        ])->assertExitCode(0);
        $this->artisan('device:tags', [
            'action' => 'get',
            'device_id' => $device->device_id,
            'tags' => ['cliowner'],
        ])->expectsOutput('cliowner=carol')->assertExitCode(0);

        // Set and get hidden tag
        $this->artisan('device:tags', [
            'action' => 'set',
            'device_id' => $device->device_id,
            'tags' => ['hiddencli=secret'],
            '--json' => true,
        ])->assertExitCode(0);
        $tagKey = \App\Models\DeviceTagKey::where('key', 'hiddencli')->first();
        $this->assertNotNull($tagKey);
        $this->assertFalse($tagKey->visible);

        // Delete tag and confirm
        $this->artisan('device:tags', [
            'action' => 'delete',
            'device_id' => $device->device_id,
            'tags' => ['cliowner'],
        ])->assertExitCode(0);
        $this->artisan('device:tags', [
            'action' => 'get',
            'device_id' => $device->device_id,
            'tags' => ['cliowner'],
        ])->expectsOutput('cliowner=(not set)')->assertExitCode(0);

        // Set and get typed tags
        \App\Models\DeviceTagKey::create(['key' => 'email_tag', 'type' => 'email', 'visible' => true]);
        $this->artisan('device:tags', [
            'action' => 'set',
            'device_id' => $device->device_id,
            'tags' => ['email_tag=user@example.com'],
        ])->assertExitCode(0);
        $this->artisan('device:tags', [
            'action' => 'get',
            'device_id' => $device->device_id,
            'tags' => ['email_tag'],
        ])->expectsOutput('email_tag=user@example.com')->assertExitCode(0);
        \App\Models\DeviceTagKey::create(['key' => 'int_tag', 'type' => 'integer', 'visible' => true]);
        $this->artisan('device:tags', [
            'action' => 'set',
            'device_id' => $device->device_id,
            'tags' => ['int_tag=123'],
        ])->assertExitCode(0);
        $this->artisan('device:tags', [
            'action' => 'get',
            'device_id' => $device->device_id,
            'tags' => ['int_tag'],
        ])->expectsOutput('int_tag=123')->assertExitCode(0);
        \App\Models\DeviceTagKey::create(['key' => 'url_tag', 'type' => 'url', 'visible' => true]);
        $this->artisan('device:tags', [
            'action' => 'set',
            'device_id' => $device->device_id,
            'tags' => ['url_tag=https://example.com'],
        ])->assertExitCode(0);
        $this->artisan('device:tags', [
            'action' => 'get',
            'device_id' => $device->device_id,
            'tags' => ['url_tag'],
        ])->expectsOutput('url_tag=https://example.com')->assertExitCode(0);
    }

    public function test_cli_set_tag_invalid_types_fail()
    {
        $device = Device::factory()->create();
        \App\Models\DeviceTagKey::create(['key' => 'email_tag', 'type' => 'email', 'visible' => true]);
        \App\Models\DeviceTagKey::create(['key' => 'int_tag', 'type' => 'integer', 'visible' => true]);
        \App\Models\DeviceTagKey::create(['key' => 'url_tag', 'type' => 'url', 'visible' => true]);
        \App\Models\DeviceTagKey::create(['key' => 'timestamp_tag', 'type' => 'timestamp', 'visible' => true]);

        $this->artisan('device:tags', [
            'action' => 'set',
            'device_id' => $device->device_id,
            'tags' => ['email_tag=not-an-email'],
        ])->expectsOutputToContain('does not match type')->assertExitCode(2);
        $this->artisan('device:tags', [
            'action' => 'set',
            'device_id' => $device->device_id,
            'tags' => ['int_tag=not-an-int'],
        ])->expectsOutputToContain('does not match type')->assertExitCode(2);
        $this->artisan('device:tags', [
            'action' => 'set',
            'device_id' => $device->device_id,
            'tags' => ['url_tag=not-a-url'],
        ])->expectsOutputToContain('does not match type')->assertExitCode(2);
        $this->artisan('device:tags', [
            'action' => 'set',
            'device_id' => $device->device_id,
            'tags' => ['timestamp_tag=not-a-timestamp'],
        ])->expectsOutputToContain('does not match type')->assertExitCode(2);
    }

    public function test_cli_define_tag_types_and_visibility()
    {
        // Define a visible string tag
        $this->artisan('device:define-tags', [
            'tags' => ['mytag'],
            '--type' => 'string',
        ])->assertExitCode(0);
        $tagKey = \App\Models\DeviceTagKey::where('key', 'mytag')->first();
        $this->assertNotNull($tagKey);
        $this->assertEquals('string', $tagKey->type);
        $this->assertTrue($tagKey->visible);

        // Define a hidden integer tag
        $this->artisan('device:define-tags', [
            'tags' => ['hiddenint'],
            '--type' => 'integer',
            '--hidden' => true,
        ])->assertExitCode(0);
        $tagKey = \App\Models\DeviceTagKey::where('key', 'hiddenint')->first();
        $this->assertNotNull($tagKey);
        $this->assertEquals('integer', $tagKey->type);
        $this->assertFalse($tagKey->visible);

        // Define multiple tags at once
        $this->artisan('device:define-tags', [
            'tags' => ['tag1', 'tag2'],
            '--type' => 'email',
        ])->assertExitCode(0);
        $tag1 = \App\Models\DeviceTagKey::where('key', 'tag1')->first();
        $tag2 = \App\Models\DeviceTagKey::where('key', 'tag2')->first();
        $this->assertNotNull($tag1);
        $this->assertNotNull($tag2);
        $this->assertEquals('email', $tag1->type);
        $this->assertEquals('email', $tag2->type);
    }
}
