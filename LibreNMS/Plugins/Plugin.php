<?php

namespace LibreNMS\Plugins;

use View;

abstract class Plugin
{
    protected $menu_view = 'menu';
    protected $settings_view= 'settings';
    protected $device_view = 'device_overview';
    protected $port_view= 'port';
    private $prefix;

    public function __construct()
    {
	View::addLocation(base_path('html/plugins'));
        $this->prefix =  $this->className() . '/resources/views/';
    }

    final protected function className()
    {
        return str_replace(__NAMESPACE__ . '\\', '', get_called_class());
    }

    final public function menu()
    {
	$classname = static::class;
	$class = new $classname();
	echo view($class->prefix . $class->menu_view, $class->menuData());
    }

    final public function settings()
    {
	echo view($this->prefix . $this->settings_view, $this->settingsData());
    }

    final public function device_overview_container()
    {
	echo view($this->prefix . $this->device_view, $this->deviceData());
    }

    final public function port_container()
    {
	echo view($this->prefix . $this->port_view, $this->portData());
    }

    
    public function menuData(): array
    {
	return [
            'title' =>$this->className(),
	];
    }

    public function settingsData(): array
    {
	return [
            'title' => $this->className(),
	];
    }
}
