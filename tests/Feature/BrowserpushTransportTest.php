<?php

namespace LibreNMS\Tests\Feature;

use App\Models\User;
use LibreNMS\Alert\Transport\Browserpush;
use LibreNMS\Tests\InMemoryDbTestCase;

final class BrowserpushTransportTest extends InMemoryDbTestCase
{
    public function testConfigTemplateListsUsersWithDuplicateRealnames(): void
    {
        $admin = User::factory()->create(['username' => 'richard', 'realname' => 'Richard Smith']);
        $readonly = User::factory()->create(['username' => 'richard-ro', 'realname' => 'Richard Smith']);
        $solo = User::factory()->create(['username' => 'solo', 'realname' => 'Solo User']);
        $noRealname = User::factory()->create(['username' => 'plainname', 'realname' => '']);

        $options = Browserpush::configTemplate()['config'][0]['options'];

        $this->assertSame(0, $options['All Users']);
        // both users sharing a realname are listed, disambiguated by username
        $this->assertSame($admin->user_id, $options['Richard Smith (richard)']);
        $this->assertSame($readonly->user_id, $options['Richard Smith (richard-ro)']);
        // a unique realname is shown as-is, and the username is used when no realname is set
        $this->assertSame($solo->user_id, $options['Solo User']);
        $this->assertSame($noRealname->user_id, $options['plainname']);
    }

    public function testConfigTemplateKeepsNumericUsernameLabel(): void
    {
        $numeric = User::factory()->create(['username' => '1001', 'realname' => '']);

        $options = Browserpush::configTemplate()['config'][0]['options'];

        $this->assertSame(0, $options['All Users']);
        // PHP casts a numeric-string key to int and array_merge renumbers
        // integer keys, so a plain merge would lose the '1001' label
        $this->assertSame($numeric->user_id, $options['1001']);
    }
}
