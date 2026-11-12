<?php

namespace LibreNMS\Tests\Unit;

use App\View\SettingPresenter;
use LibreNMS\Tests\TestCase;

final class SettingPresenterTest extends TestCase
{
    public function testTranslatesSetting(): void
    {
        $presented = SettingPresenter::present([
            'name' => 'auth.socialite.redirect',
            'type' => 'boolean',
            'value' => true,
            'default' => false,
            'units' => 'seconds',
        ]);

        $this->assertSame('auth.socialite.redirect', $presented['name']);
        $this->assertSame(__('settings.settings.auth.socialite.redirect.description'), $presented['description']);
        $this->assertSame(__('settings.settings.auth.socialite.redirect.help'), $presented['help']);
        $this->assertSame(__('settings.units.seconds'), $presented['units']);
        $this->assertTrue($presented['value']);
        $this->assertFalse($presented['default']);
        $this->assertFalse($presented['overridden']);
    }

    public function testFallsBackToName(): void
    {
        $presented = SettingPresenter::present([
            'name' => 'not.a.real.setting',
            'type' => 'text',
            'units' => 'parsecs',
        ]);

        $this->assertSame('not.a.real.setting', $presented['description']);
        $this->assertNull($presented['help']);
        $this->assertSame('parsecs', $presented['units']);
    }

    public function testOverriddenAddsReadonlyHelp(): void
    {
        $presented = SettingPresenter::present([
            'name' => 'not.a.real.setting',
            'type' => 'text',
            'overridden' => true,
        ]);

        $this->assertTrue($presented['overridden']);
        $this->assertSame(__('settings.readonly'), $presented['help']);
    }

    public function testSelectOptionsAreOrderedList(): void
    {
        $presented = SettingPresenter::present([
            'name' => 'alert.scheduled_maintenance_default_behavior',
            'type' => 'select',
            'options' => [1 => 'Skip alerts', 2 => 'Mute alerts', 3 => 'Run alerts'],
        ]);

        $this->assertSame([
            ['value' => '1', 'text' => __('settings.settings.alert.scheduled_maintenance_default_behavior.options.1')],
            ['value' => '2', 'text' => __('settings.settings.alert.scheduled_maintenance_default_behavior.options.2')],
            ['value' => '3', 'text' => __('settings.settings.alert.scheduled_maintenance_default_behavior.options.3')],
        ], $presented['options']);
    }

    public function testMultipleOptionsAcceptCollections(): void
    {
        $presented = SettingPresenter::present([
            'name' => 'poller_groups',
            'type' => 'multiple',
            'options' => collect([0 => 'General', 5 => 'Remote']),
        ], 'poller.settings');

        $this->assertSame([
            ['value' => '0', 'text' => 'General'],
            ['value' => '5', 'text' => 'Remote'],
        ], $presented['options']);
        $this->assertSame(__('poller.settings.settings.poller_groups.description'), $presented['description']);
    }

    public function testOtherOptionsPassThrough(): void
    {
        $presented = SettingPresenter::present([
            'name' => 'default_poller_group',
            'type' => 'select-dynamic',
            'options' => ['target' => 'poller-group'],
        ]);

        $this->assertSame(['target' => 'poller-group'], $presented['options']);
    }
}
