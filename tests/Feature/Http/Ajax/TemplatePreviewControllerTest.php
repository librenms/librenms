<?php

namespace LibreNMS\Tests\Feature\Http\Ajax;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

final class TemplatePreviewControllerTest extends DBTestCase
{
    use DatabaseTransactions;

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
