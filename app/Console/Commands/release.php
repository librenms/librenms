<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LibreNMS\Util\GitHub;

class release extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'release:tag {tag} {from} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new LibreNMS release including changelog';

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
     * @return mixed
     */
    public function handle()
    {
        $tag   = $this->argument('tag');
        $from  = $this->argument('from');
        $file  = $this->argument('file');
        $token = getenv('GH_TOKEN') ?: $this->secret('Enter a GitHub Token?');

        $gh = new GitHub($tag, $from, $file, $token);
        $this->info("Creating release $tag.....");
        $gh->createRelease();
    }
}
