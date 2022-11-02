<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Version;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array $commands
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');

        if ($this->app->environment() !== 'production') {
            require base_path('routes/dev-console.php');
        }
    }

    public function getArtisan(): ?\Illuminate\Console\Application
    {
        if (is_null($this->artisan)) {
            parent::getArtisan();
            /** @phpstan-ignore-next-line */
            $this->artisan->setName('LibreNMS');
            /** @phpstan-ignore-next-line */
            $this->artisan->setVersion(Version::VERSION);
        }

        return $this->artisan;
    }

    public function handle(\Symfony\Component\Console\Input\InputInterface $input, ?\Symfony\Component\Console\Output\OutputInterface $output = null): int
    {
        // intercept input and check for debug
        if ($input->hasParameterOption(['-d', '--debug', '-vv', '-vvv'], true)) {
            if ($input->hasParameterOption(['-vvv'], true)) {
                Debug::setVerbose();
            }
            $this->app->booted('\LibreNMS\Util\Debug::set');
        }

        return parent::handle($input, $output);
    }
}
