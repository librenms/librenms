<?php

namespace App\Observers;

use App\Events\SettingChanged;
use App\Facades\LibrenmsConfig;
use App\Models\Config;

class ConfigObserver
{
    public function saved(Config $config): void
    {
        event("setting.changed.$config->config_name", new SettingChanged($config->config_name, $config->config_value));
    }

    /**
     * Handle the config "deleted" event.
     */
    public function deleted(Config $config): void
    {
        LibrenmsConfig::invalidateCache();
        event("setting.changed.$config->config_name", new SettingChanged($config->config_name, LibrenmsConfig::get($config->config_name)));
    }
}
