<?php

use Illuminate\Database\Seeder;

class DefaultPortDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('port_association_mode')->insert([
            [
                'name'  => 'ifIndex',
            ],
            [
                'name'  => 'ifName',
            ],
            [
                'name'  => 'ifDescr',
            ],
            [
                'name'  => 'ifAlias',
            ]
        ]);
    }
}
