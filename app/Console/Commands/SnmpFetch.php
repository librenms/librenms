<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\Device;
use LibreNMS\Util\Debug;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class SnmpFetch extends LnmsCommand
{
    protected $name = 'snmp:fetch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('device spec', InputArgument::REQUIRED);
        $this->addArgument('oid', InputArgument::REQUIRED);
        $this->addOption('type', 't', InputOption::VALUE_REQUIRED, trans('commands.snmp:fetch.options.type', ['types' => '[get, walk, next, translate]']), 'get');
        $this->addOption('output', 'o', InputOption::VALUE_REQUIRED, trans('commands.snmp:fetch.options.output', ['formats' => '']), '[value, values, table]');
        $this->addOption('depth', 'd', InputOption::VALUE_REQUIRED);
        $this->addOption('numeric', 'n', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Debug::set();

        $type = $this->option('type');
        $type = in_array($type, ['walk', 'get', 'next', 'translate']) ? $type : 'get';

        $spec = $this->argument('device spec');
        $device_id = Device::where('device_id', $spec)->orWhere('hostname', $spec)->value('device_id');
        \DeviceCache::setPrimary($device_id);

        $res = \NetSnmp::options([])
        ->$type($this->argument('oid'));

        dump($res->isValid());
        dump($res->raw());
        dump($res->value());
        dump($res->values());
        dump($res->table($this->argument('depth')));

        return 0;
    }
}
