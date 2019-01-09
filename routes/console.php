<?php

use App\Jobs\PingCheck;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('ping {--d|debug} {groups?* : Optional List of distributed poller groups to poll}', function () {
    $this->alert("Do not use this command yet, use ./ping.php");
//    PingCheck::dispatch(new PingCheck($this->argument('groups')));
})->describe('Check if devices are up or down via icmp');
