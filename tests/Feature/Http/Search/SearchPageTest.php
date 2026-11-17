<?php

namespace LibreNMS\Tests\Feature\Http\Search;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Port;
use App\Models\PortsFdb;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Support\Facades\DB;

class SearchPageTest extends SearchTestCase
{
    public function testPageRendersFilterBar(): void
    {
        $this->actingAs($this->admin())
            ->get(route('search', ['q' => '10.0.0.1']))
            ->assertOk()
            ->assertViewHas('query', '10.0.0.1')
            ->assertViewHas('hasQuery', true)
            ->assertViewHas('filterFields', fn ($fields) => array_column($fields, 'key') === ['source', 'device_id', 'device.groups.id', 'port_id', 'ifAlias', 'vlan', 'ifType'])
            ->assertViewHas('resultsUrl', route('search.results'))
            ->assertSeeInOrder([__('search.mac_format'), __('search.per_page')])
            ->assertSee("window.Alpine.data('searchHost'", false);

        $this->get(url('about'))->assertOk()->assertDontSee("window.Alpine.data('searchHost'", false);
    }

    public function testSavedFiltersAreApplied(): void
    {
        $admin = $this->admin();
        UserPref::setPref($admin, 'filters.network-search', ['vlan' => ['eq' => '120']]);

        $this->actingAs($admin)->get(route('search'))->assertViewHas('filter', ['vlan' => ['eq' => '120']]);
    }

    public function testLegacyUrlsRedirect(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('search/search=arp/searchPhrase=10.0.0.1')
            ->assertRedirect(route('search', ['q' => '10.0.0.1', 'filter' => ['source' => ['eq' => 'neighbor']]]));
        $this->actingAs($admin)->post('search/search=ipv4/', ['address' => '10.1.', 'device_id' => 3])
            ->assertRedirect(route('search', ['q' => '10.1.', 'filter' => ['source' => ['eq' => 'interface'], 'device_id' => ['eq' => 3]]]));
        $this->actingAs($admin)->get('search/search=fdb')
            ->assertRedirect(route('search', ['q' => '', 'filter' => ['source' => ['eq' => 'fdb']]]));
        $this->actingAs($admin)->get('search/search=packages')
            ->assertRedirect(route('packages.index'));
    }

    public function testResultsRequirePortAccess(): void
    {
        $user = User::factory()->create(['enabled' => 1]);

        $this->actingAs($user)->getJson(route('search.results', ['q' => '10.0.0.1']))->assertForbidden();
    }

    public function testRemembersMacFormat(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('preferences.store'), ['pref' => 'network_search_mac_format', 'value' => 'dot'])->assertOk();

        $this->actingAs($admin)->get(route('search'))->assertViewHas('macFormat', 'dot');
    }

    public function testGlobalSearchGroupsLinkToAllResults(): void
    {
        $device = Device::factory()->create(['hostname' => 'viewall-test.example.com']);
        $port = Port::factory()->for($device)->create();
        PortsFdb::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '02facade0001']);
        LibrenmsConfig::set('webui.global_search.fdb', true);
        $admin = $this->admin();

        $this->actingAs($admin)->getJson(route('ajax.search.devices', ['search' => 'viewall-test']))
            ->assertJsonPath('groups.0.url', route('devices', ['filter' => ['search' => ['contains' => 'viewall-test']]]));
        $this->actingAs($admin)->getJson(route('ajax.search.fdb', ['search' => '02:fa:ca:de:00:01']))
            ->assertJsonPath('groups.0.url', route('search', ['q' => '02:fa:ca:de:00:01', 'filter' => ['source' => ['eq' => 'fdb']]]));
        // too short to be a MAC on the search page, so there is nothing to link to
        $this->actingAs($admin)->getJson(route('ajax.search.fdb', ['search' => 'faca']))
            ->assertJsonPath('groups.0.type', 'fdb_tables')
            ->assertJsonPath('groups.0.url', null);
        // deciding on the link doesn't look up vendors on every keystroke
        DB::enableQueryLog();
        $this->actingAs($admin)->getJson(route('ajax.search.fdb', ['search' => 'faca']))->assertOk();
        $this->assertEmpty(array_filter(DB::getQueryLog(), fn (array $query) => str_contains($query['query'], 'vendor_ouis')));
    }

    public function testPageRequiresPortAccess(): void
    {
        $user = User::factory()->create(['enabled' => 1]);

        $this->actingAs($user)->get(route('search'))->assertForbidden();
        $this->actingAs($user)->get(route('search.export'))->assertForbidden();
    }

    public function testMenuLinkIsUnderOverviewAndFollowsPortAccess(): void
    {
        $this->portOn();
        $link = 'href="' . route('search') . '"';

        $this->actingAs($this->admin())->get(route('search'))->assertSeeInOrder([$link, 'href="' . route('ports') . '"'], false);

        $user = User::factory()->create(['enabled' => 1]);
        $this->actingAs($user)->get(url('about'))->assertOk()->assertDontSee($link, false);
        $this->actingAs($user)->get(route('search'))->assertForbidden();

        $user->assignRole('user');
        $this->actingAs($user->fresh())->get(url('about'))->assertOk()->assertSee($link, false);
        $this->actingAs($user->fresh())->get(route('search'))->assertOk();
    }
}
