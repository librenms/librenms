<?php

namespace Tests\Unit;

use App\Services\WidgetRegistry;
use Illuminate\Contracts\Foundation\Application;
use LibreNMS\Interfaces\Plugins\Hooks\DashboardWidgetHook;
use LibreNMS\Tests\TestCase;
use Mockery;

class WidgetRegistryTest extends TestCase
{
    public function testCoreWidgetsAreRegistered()
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('tagged')->with('librenms.widget')->andReturn([]);

        $registry = new WidgetRegistry($app);

        $widgets = $registry->getWidgets();

        // Test that core widgets are registered
        $this->assertTrue($widgets->has('alerts'));
        $this->assertTrue($widgets->has('alertlog'));
        $this->assertTrue($widgets->has('worldmap'));

        // Test widget structure
        $alertsWidget = $widgets->get('alerts');
        $this->assertEquals('alerts', $alertsWidget['name']);
        $this->assertEquals('core', $alertsWidget['type']);
        $this->assertArrayHasKey('title', $alertsWidget);
        $this->assertArrayHasKey('controller', $alertsWidget);
    }

    public function testPluginWidgetsAreRegistered()
    {
        $mockHook = Mockery::mock(DashboardWidgetHook::class);
        $mockHook->shouldReceive('getWidgetName')->andReturn('test-widget');
        $mockHook->shouldReceive('getWidgetTitle')->andReturn('Test Widget');
        $mockHook->shouldReceive('getWidgetController')->andReturn('TestController');

        $app = Mockery::mock(Application::class);
        $app->shouldReceive('tagged')->with('librenms.widget')->andReturn([$mockHook]);

        $registry = new WidgetRegistry($app);

        $widgets = $registry->getWidgets();

        // Test that plugin widget is registered
        $this->assertTrue($widgets->has('test-widget'));

        $testWidget = $widgets->get('test-widget');
        $this->assertEquals('test-widget', $testWidget['name']);
        $this->assertEquals('Test Widget', $testWidget['title']);
        $this->assertEquals('TestController', $testWidget['controller']);
        $this->assertEquals('plugin', $testWidget['type']);
    }

    public function testConflictingWidgetNamesAreSkipped()
    {
        $mockHook = Mockery::mock(DashboardWidgetHook::class);
        $mockHook->shouldReceive('getWidgetName')->andReturn('alerts'); // Conflicts with core widget
        $mockHook->shouldReceive('getWidgetTitle')->andReturn('Conflicting Widget');
        $mockHook->shouldReceive('getWidgetController')->andReturn('ConflictingController');

        $app = Mockery::mock(Application::class);
        $app->shouldReceive('tagged')->with('librenms.widget')->andReturn([$mockHook]);

        $registry = new WidgetRegistry($app);

        $widgets = $registry->getWidgets();

        // Test that the core widget is preserved, not the conflicting plugin widget
        $alertsWidget = $widgets->get('alerts');
        $this->assertEquals('core', $alertsWidget['type']);
        $this->assertNotEquals('Conflicting Widget', $alertsWidget['title']);
    }

    public function testGetWidgetNames()
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('tagged')->with('librenms.widget')->andReturn([]);

        $registry = new WidgetRegistry($app);

        $widgetNames = $registry->getWidgetNames();

        $this->assertIsArray($widgetNames);
        $this->assertContains('alerts', $widgetNames);
        $this->assertContains('worldmap', $widgetNames);
    }

    public function testGetWidgetTitles()
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('tagged')->with('librenms.widget')->andReturn([]);

        $registry = new WidgetRegistry($app);

        $widgetTitles = $registry->getWidgetTitles();

        $this->assertIsArray($widgetTitles);
        $this->assertArrayHasKey('alerts', $widgetTitles);
        $this->assertArrayHasKey('worldmap', $widgetTitles);
    }

    public function testHasWidget()
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('tagged')->with('librenms.widget')->andReturn([]);

        $registry = new WidgetRegistry($app);

        $this->assertTrue($registry->hasWidget('alerts'));
        $this->assertFalse($registry->hasWidget('non-existent-widget'));
    }

    public function testGetWidget()
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('tagged')->with('librenms.widget')->andReturn([]);

        $registry = new WidgetRegistry($app);

        $widget = $registry->getWidget('alerts');
        $this->assertNotNull($widget);
        $this->assertEquals('alerts', $widget['name']);

        $nonExistent = $registry->getWidget('non-existent-widget');
        $this->assertNull($nonExistent);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
