<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\CompletesConfigArgument;
use App\Console\LnmsCommand;
use LibreNMS\Config;
use LibreNMS\DB\Eloquent;
use LibreNMS\Util\DynamicConfig;
use Symfony\Component\Console\Input\InputArgument;

class SetConfigCommand extends LnmsCommand
{
    use CompletesConfigArgument;

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
        $this->addOption('ignore-checks');
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
        $force = $this->option('ignore-checks');

        if (! $force && ! $definition->isValidSetting($setting)) {
            $this->error(trans('commands.config:set.errors.invalid'));

            return 2;
        }

        if (! Eloquent::isConnected()) {
            $this->error(trans('commands.config:set.errors.nodb'));

            return 1;
        }

        if (! $force && ! $value) {
            if ($this->confirm(trans('commands.config:set.confirm', ['setting' => $setting]))) {
                Config::erase($setting);

                return 0;
            }

            return 3;
        }

        $value = $this->juggleType($value);
        $configItem = $definition->get($setting);
        if (! $force && ! $configItem->checkValue($value)) {
            $message = ($configItem->type || $configItem->validate)
                ? $configItem->getValidationMessage($value)
                : trans('commands.config:set.errors.no-validation', ['setting' => $setting]);
            $this->error($message);

            return 2;
        }

        if (Config::persist($setting, $value)) {
            return 0;
        }

        $this->error(trans('commands.config:set.errors.failed', ['setting' => $setting]));

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
