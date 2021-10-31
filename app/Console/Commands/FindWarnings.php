<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class FindWarnings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'find:warnings {regex? : regex to match snmprec files (default /.*/)}';

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
     * @var bool
     */
    private $found = false;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // TODO DELETE ME

        $regex = $this->argument('regex') ?: '/./';
        $modules = 'core,isis,mempools,mpls,nac,netstats,os,printer-supplies,slas';

        foreach (glob(base_path('tests/snmpsim/*.snmprec')) as $file) {
            if ($this->found) {
                break;
            }

            $community = basename($file, '.snmprec');
            if (preg_match($regex, $community)) {
                $this->addDevice($community);
                $this->info($community);

                $process = new Process(['./discovery.php', '-d', '-h', 'snmpsim', '-m', $modules]);
                $process->run(function ($type, $buffer) {
                    if (Str::contains($buffer, 'Warning:')) {
                        $this->error($buffer);
                        $this->found = true;
                    }
                });

                $process = new Process(['./poller.php', '-d', '-h', 'snmpsim', '-m', $modules]);
                $process->run(function ($type, $buffer) {
                    if (Str::contains($buffer, 'Warning:')) {
                        $this->error($buffer);
                        $this->found = true;
                    }
                });

            }
        }

        return Command::SUCCESS;
    }

    private function findWarning(string $buffer) {
        if (Str::contains($buffer, 'Warning:')) {
            preg_match_all('/^Warning: .*$/', $buffer, $matches);

            $this->error(implode(PHP_EOL, $matches[0]));
            $this->found = true;
        }
    }

    private function addDevice($community)
    {
        $device = Device::firstOrNew(['hostname' => 'snmpsim']);
        $device->overwrite_ip = '127.1.6.1';
        $device->port = 1161;
        $device->snmpver = 'v2c';
        $device->transport = 'udp';
        $device->community = $community;
        $device->last_discovered = null;
        $device->status_reason = '';
        $device->save();
    }
}
