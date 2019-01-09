<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(DefaultAlertTemplateSeeder::class);
        $this->call(DefaultConfigSeeder::class);
        $this->call(DefaultWidgetSeeder::class);
        $this->call(DefaultLegacySchemaSeeder::class);
    }
}
