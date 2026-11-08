<?php

namespace LibreNMS\Tests\Browser;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use LibreNMS\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Permission\Models\Role;

#[Group('browser')]
final class LocationsPageTest extends DuskTestCase
{
    use DatabaseTruncation;
    /** @var list<string> */
    protected array $connectionsToTruncate = ['testing', 'testing_persistent'];

    /**
     * @throws \Throwable
     */
    public function testLocationNamesAreEscapedInPage(): void
    {
        $name = '"><script>alert(1)</script>';
        Role::findOrCreate('admin');
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('admin');
        Location::factory()->create(['location' => $name]);

        $this->browse(function (Browser $browser) use ($user, $name): void {
            $browser->loginAs($user)
                ->visit('/locations')
                ->waitFor('#locations tbody tr td a')
                ->assertSeeIn('#locations tbody tr td a', $name);

            // the name is shown as text and was not interpreted as markup
            $this->assertSame($name, $browser->text('#locations tbody tr td a'));
            $this->assertSame(0, $browser->script('return document.querySelectorAll("#locations script").length')[0]);
        });
    }
}
