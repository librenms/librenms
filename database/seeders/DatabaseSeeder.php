<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DefaultAlertTemplateSeeder::class);
        $this->call(DefaultWidgetSeeder::class);
        $this->call(DefaultLegacySchemaSeeder::class);
    }
}
