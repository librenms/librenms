<?php

namespace App\Console\Commands;

use App\Jobs\PingCheck;
use Illuminate\Console\Command;

class Ping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ping {--d|debug} {groups?* | Optional List of distributed poller groups to poll}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if devices are up or down via icmp';

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
     * @return void
     */
    public function handle()
    {
        $this->alert("Do not use this command yet, use ./ping.php");
        exit();

        PingCheck::dispatch(new PingCheck($this->argument('groups')));
    }
}
