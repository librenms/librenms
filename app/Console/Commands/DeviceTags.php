<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeviceTags extends LnmsCommand
{
    protected $name = 'device:tags';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->addArgument('action', InputArgument::REQUIRED, 'Action to perform (get, set, delete,)', null, ['get', 'set', 'delete']);
        $this->addArgument('device_id', InputArgument::REQUIRED);
        $this->addArgument('tags', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'List of space seperated tags (key1=value key2=value) for set operations\nlist of keys for get or delete operations (key1 key2)');
        $this->addOption('json', 'j', InputOption::VALUE_NONE, 'Output results as JSON');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $device_id = $this->argument('device_id');
        $action = $this->argument('action');
        $tags = $this->argument('tags');

        $device = Device::find($device_id);
        if (! $device) {
            $this->error("Device not found: $device_id");

            return 1;
        }

        if ($action === 'set') {
            $pairs = [];
            foreach ($tags as $tag) {
                if (strpos($tag, '=') !== false) {
                    [$k, $v] = explode('=', $tag, 2);
                    $pairs[$k] = $v;
                }
            }

            if ($pairs) {
                $this->renderOutput($device->setTag($pairs));
            }

            return 0;
        }

        if ($action === 'get') {
            $this->renderOutput($device->getTag($tags));

            return 0;
        }

        if ($action === 'delete') {
            $this->renderOutput($device->deleteTag($tags));

            return 0;
        }

        $this->error("Unknown action: $action (expected get, set, or delete)");

        return 2;
    }

    private function renderOutput($tags): void
    {
        if ($tags) {
            if ($json = $this->option('json')) {
                $this->line(json_encode($tags));

                return;
            }

            foreach ($tags as $key => $value) {
                $this->line("$key=$value");
            }
        }
    }
}
