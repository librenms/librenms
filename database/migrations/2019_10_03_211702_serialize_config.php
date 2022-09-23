<?php

use Illuminate\Database\Migrations\Migration;

class SerializeConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('config')->get()->each(function ($config) {
            $value = $config->config_value;

            if (filter_var($value, FILTER_VALIDATE_INT)) {
                $value = (int) $value;
            } elseif (filter_var($value, FILTER_VALIDATE_FLOAT)) {
                $value = (float) $value;
            } elseif (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }

            DB::table('config')
                ->where('config_id', $config->config_id)
                ->update(['config_value' => json_encode($value)]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('config')->get()->each(function ($config) {
            $value = json_decode($config->config_value);
            $value = is_bool($value) ? var_export($value, true) : (string) $value;

            DB::table('config')
                ->where('config_id', $config->config_id)
                ->update(['config_value' => $value]);
        });
    }
}
