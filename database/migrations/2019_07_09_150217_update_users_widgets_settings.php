<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\UserWidget;

class UpdateUsersWidgetsSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $widgets = UserWidget::where('widget_id', 1)->get();
        foreach ($widgets as $widget) {
            $settings = $widget->settings;

            $settings["device_group"] = $settings["group"];
            unset($settings["group"]);

            $widget->settings = $settings;
            $widget->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $widgets = UserWidget::where('widget_id', 1)->get();
        foreach ($widgets as $widget) {
            $settings = $widget->settings;

            $settings["group"] = $settings["device_group"];
            unset($settings["device_group"]);

            $widget->settings = $settings;
            $widget->save();
        }
    }
}
