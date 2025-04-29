<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use LibreNMS\Util\EnvHelper;
use LibreNMS\Util\Http;
use LibreNMS\ValidationResult;
use LibreNMS\Validations\Database\CheckDatabaseConnected;
use LibreNMS\Validations\Poller\CheckRedis;

class HealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'health:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Container Health Check';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        //check redis
        $redisResult = (new CheckRedis)->validate();
        $redisStatus = $redisResult->getStatus();
        if ($redisStatus == ValidationResult::WARNING) {
            $this->warn($redisResult->getMessage());
        } elseif ($redisStatus == ValidationResult::FAILURE) {
            $this->error($redisResult->getMessage());

            return 1;
        }

        // check database
        $dbResult = (new CheckDatabaseConnected)->validate();
        $dbStatus = $dbResult->getStatus();
        if ($dbStatus == ValidationResult::FAILURE) {
            $this->error($dbResult->getMessage() . ($dbResult->getList()[0] ?? ''));

            return 1;
        }

        // docker specific checks
        if (EnvHelper::librenmsDocker()) {
            if (getenv('SIDECAR_DISPATCHER')) {
                // check dispatcher
                $health_file = \LibreNMS\Config::get('service_health_file');

                if (! $health_file) {
                    $this->warn('Dispatcher service health file not enabled, set service_health_file');

                    return 0;
                }

                if (! file_exists($health_file)) {
                    $this->error('Dispatcher service not started yet');

                    return 1;
                }

                if (filemtime($health_file) < (time() - 30)) {
                    $this->error('Dispacher service missed three heartbeats');

                    return 1;
                }
            } else {
                // check webui
                try {
                    $response = Http::client()->get('http://localhost:8000');

                    if (! $response->successful()) {
                        $this->error($response->reason());

                        return 1;
                    }
                } catch (ConnectionException $e) {
                    $this->error($e->getMessage());

                    return 1;
                }
            }
        }

        return 0; // all ok
    }
}
