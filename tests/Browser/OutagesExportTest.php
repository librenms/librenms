<?php

namespace LibreNMS\Tests\Browser;

use App\Models\Device;
use App\Models\DeviceOutage;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use LibreNMS\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('browser')]
final class OutagesExportTest extends DuskTestCase
{
    use DatabaseTruncation;

    /** @var list<string> */
    protected array $connectionsToTruncate = ['testing', 'testing_persistent'];

    public function testExportUsesTheLastDisplayedOutageFilters(): void
    {
        $user = $this->browserUser('admin');
        $device = Device::factory()->create(['disabled' => 0]);
        DeviceOutage::factory()->for($device)->open()->create(['going_down' => now()->subHour()->timestamp]);

        $this->browse(function (Browser $browser) use ($user, $device): void {
            $browser->loginAs($user)
                ->visitRoute('outages', ['device' => $device->device_id, 'refresh' => 0])
                ->waitFor('#outages tbody a');

            $browser->script(<<<'JS'
                window.outageExports = [];
                window.open = url => window.outageExports.push(url);

                $.ajaxPrefilter(options => {
                    if (options.url !== document.querySelector('#outages').dataset.url) {
                        return;
                    }

                    const success = options.success;
                    options.success = (...args) => {
                        window.finishOutagesReload = () => success(...args);
                    };
                });
                JS);

            $browser->select('#status', 'previous')
                ->waitUntil('window.finishOutagesReload !== undefined');

            [$pendingPage, $pendingAll, $loadedPage] = $browser->script(<<<'JS'
                const exportPage = document.querySelector('#outages-export-button [data-export-type=visible]');
                const exportAll = document.querySelector('#outages-export-button [data-export-type=all]');
                exportPage.click();
                exportAll.click();
                window.finishOutagesReload();
                exportPage.click();
                return window.outageExports;
                JS)[0];

            $exportPath = route('table.outages.export', absolute: false);
            foreach ([$pendingPage, $pendingAll, $loadedPage] as $exportUrl) {
                $this->assertSame($exportPath, parse_url($exportUrl, PHP_URL_PATH));
            }

            parse_str(parse_url($pendingPage, PHP_URL_QUERY), $pendingPageParams);
            parse_str(parse_url($pendingAll, PHP_URL_QUERY), $pendingAllParams);
            parse_str(parse_url($loadedPage, PHP_URL_QUERY), $loadedPageParams);

            $this->assertSame('current', $pendingPageParams['status']);
            $this->assertSame((string) $device->device_id, $pendingPageParams['device']);
            $this->assertSame(['going_down' => 'desc'], $pendingPageParams['sort']);
            $this->assertSame('1', $pendingPageParams['current']);
            $this->assertSame('50', $pendingPageParams['rowCount']);

            unset($pendingPageParams['current'], $pendingPageParams['rowCount']);
            $this->assertSame($pendingPageParams, $pendingAllParams);
            $this->assertSame('previous', $loadedPageParams['status']);
        });
    }
}
