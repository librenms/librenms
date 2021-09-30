<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use LibreNMS\Polling\SNMP;
use LibreNMS\Util\Debug;

class SnmpFetch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snmp:fetch {device spec} {oid} {depth=0} {--type=get}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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

        $snmp = new SNMP();
        $res = $snmp
//            ->options('-On')
        ->$type($this->argument('oid'));

        dump($res->isValid());
        dump($res->raw());
        dump($res->value());
        dump($res->values());
        dump($res->table($this->argument('depth')));

        return 0;
    }
}
