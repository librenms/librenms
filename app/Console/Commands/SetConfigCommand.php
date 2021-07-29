<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\CompletesConfigArgument;
use App\Console\LnmsCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
        $parent = null;

        if (! $definition->isValidSetting($setting)) {
            $parent = $this->findParentSetting($definition, $setting);
            if (! $force && ! $parent) {
                $this->error(trans('commands.config:set.errors.invalid'));

                return 2;
            }
        }

        if (! Eloquent::isConnected()) {
            $this->error(trans('commands.config:set.errors.nodb'));

            return 1;
        }

        if (! $force && ! $value) {
            $message = $parent
                ? trans('commands.config:set.forget_from', ['path' => $this->getChildPath($setting, $parent), 'parent' => $parent])
                : trans('commands.config:set.confirm', ['setting' => $setting]);

            if ($this->confirm($message)) {
                return $this->erase($setting, $parent) ? 0 : 1;
            }

            return 3;
        }

        $value = $this->juggleType($value);

        // handle appending to arrays
        if (Str::endsWith($setting, '.+')) {
            $setting = substr($setting, 0, -2);
            $sub_data = Config::get($setting, []);
            if (! is_array($sub_data)) {
                $this->error(trans('commands.config:set.errors.append'));

                return 2;
            }

            array_push($sub_data, $value);
            $value = $sub_data;
        }

        // handle setting value inside multi-dimensional array
        if ($parent) {
            $parent_data = Config::get($parent);
            Arr::set($parent_data, $this->getChildPath($setting, $parent), $value);
            $value = $parent_data;
            $setting = $parent;
        }

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
     * @return mixed
     */
    private function juggleType(string $value)
    {
        $json = json_decode($value, true);

        return json_last_error() ? $value : $json;
    }

    private function findParentSetting(DynamicConfig $definition, $setting): ?string
    {
        $parts = explode('.', $setting);
        array_pop($parts); // looking for parent, not this setting

        while (! empty($parts)) {
            $name = implode('.', $parts);
            if ($definition->isValidSetting($name)) {
                return $name;
            }
            array_pop($parts);
        }

        return null;
    }

    private function erase($setting, $parent = null)
    {
        if ($parent) {
            $data = Config::get($parent);

            if (preg_match("/^$parent\.?(?<sub>.+)\\.(?<index>\\d+)\$/", $setting, $matches)) {
                // nested inside the parent setting, update just the required part
                $sub_data = Arr::get($data, $matches['sub']);
                $this->forgetWithIndex($sub_data, $matches['index']);
                Arr::set($data, $matches['sub'], $sub_data);
            } else {
                // not nested, just forget the setting
                $this->forgetWithIndex($data, $this->getChildPath($setting, $parent));
            }

            return Config::persist($parent, $data);
        }

        return Config::erase($setting);
    }

    private function getChildPath($setting, $parent = null): string
    {
        return ltrim(Str::after($setting, $parent), '.');
    }

    private function hasSequentialIndex($array): bool
    {
        if (! is_array($array) || $array === []) {
            return false;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }

    private function forgetWithIndex(&$data, $matches)
    {
        // detect sequentially numeric indexed array so we can re-index the array
        if ($this->hasSequentialIndex($data)) {
            array_splice($data, (int) $matches, 1);
        } else {
            Arr::forget($data, $matches);
        }
    }
}
