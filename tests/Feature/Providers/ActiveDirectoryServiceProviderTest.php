<?php

namespace LibreNMS\Tests\Feature\Providers;

use App\Facades\LibrenmsConfig;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\BootProviders;
use LibreNMS\Tests\TestCase;

class ActiveDirectoryServiceProviderTest extends TestCase
{
    public function createApplication(): Application
    {
        $app = require self::basePath('bootstrap/app.php');
        $app->beforeBootstrapping(BootProviders::class, function (Application $app): void {
            config([
                'database.default' => 'testing_memory',
                'librenms.config_cache_ttl' => 0,
            ]);
            $connection = $app->make('db')->connection();
            $connection->getSchemaBuilder()->create('config', function (Blueprint $table): void {
                $table->increments('config_id');
                $table->string('config_name');
                $table->text('config_value');
            });
            $connection->table('config')->insert([
                'config_name' => 'twofactor',
                'config_value' => 'true',
            ]);
        });
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function testDatabaseSettingsAreLoadedDuringApplicationBootstrap(): void
    {
        $this->assertTrue(LibrenmsConfig::get('twofactor'));
    }
}
