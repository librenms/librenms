<?php

namespace LibreNMS\Tests\Feature\Http\Ajax;

use App\Models\User;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class TemplatePreviewControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->dbSetUp();
    }

    protected function tearDown(): void
    {
        $this->dbTearDown();
        parent::tearDown();
    }

    public function testUnauthenticatedUserCannotPreviewTemplate(): void
    {
        $response = $this->postJson(route('ajax.template.preview'), [
            'template' => '{{ hostname }}',
        ]);

        $response->assertUnauthorized();
    }

    public function testAuthenticatedUserCanPreviewTemplate(): void
    {
        $user = User::factory()->create(['enabled' => 1]);

        $response = $this->actingAs($user)->postJson(route('ajax.template.preview'), [
            'template' => '{{ hostname|lower }} ({{ ip }})',
            'variables' => [
                'hostname' => 'SWITCH01.EXAMPLE.COM',
                'ip' => '10.0.0.1',
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'preview' => 'switch01.example.com (10.0.0.1)',
        ]);
    }

    public function testPreviewHandlesMissingVariables(): void
    {
        $user = User::factory()->create(['enabled' => 1]);

        $response = $this->actingAs($user)->postJson(route('ajax.template.preview'), [
            'template' => '{{ hostname }} - {{ missing }}',
            'variables' => [
                'hostname' => 'switch01',
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'preview' => 'switch01 - ',
        ]);
    }
}
