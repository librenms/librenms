<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use LibreNMS\Config;
use LibreNMS\DB\Eloquent;
use LibreNMS\Util\DynamicConfig;
use Symfony\Component\Console\Input\InputArgument;

class SetConfigCommand extends LnmsCommand
{
    protected $name = 'config:set';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->addArgument('setting', InputArgument::REQUIRED);
        $this->addArgument('value', InputArgument::OPTIONAL);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(DynamicConfig $definition)
    {
        $setting = $this->argument('setting');
        $value = $this->argument('value');

        if (!$definition->isValidSetting($setting)) {
            $this->error(__('This is not a valid setting. Please check your spelling'));
            return 2;
        }

        if (!Eloquent::isConnected()) {
            $this->error(__('Database is not connected'));
            return 1;
        }

        if (!$value) {
            if ($this->confirm(__('Reset :setting to the default?', ['setting' => $setting]))) {
                Config::erase($setting);
                return 0;
            }
            return 3;
        }

        $value = $this->juggleType($value);
        $configItem = $definition->get($setting);
        if (!$configItem->checkValue($value)) {
            $message = ($configItem->type || $configItem->validate)
                ? $configItem->getValidationMessage($value)
                : __('Cannot set :setting, it is missing validation definition.', ['setting' => $setting]);
            $this->error($message);
            return 2;
        }

        if (Config::persist($setting, $value)) {
            return 0;
        }

        $this->error(__('Failed to set :setting', ['setting' => $setting]));
        return 1;
    }

    /**
     * Convert the string input into the appropriate PHP native type
     *
     * @param $value
     * @return mixed
     */
    private function juggleType($value)
    {
        $json = json_decode($value, true);
        return json_last_error() ? $value : $json;
    }
}
