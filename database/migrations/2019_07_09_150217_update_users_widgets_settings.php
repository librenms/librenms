<?php

use App\Models\UserWidget;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersWidgetsSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** @phpstan-ignore-next-line */
        $widgets = UserWidget::where('widget_id', 1)->get();
        foreach ($widgets as $widget) {
            $settings = $widget->settings;

            $settings['device_group'] = $settings['group'];
            unset($settings['group']);

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
        /** @phpstan-ignore-next-line */
        $widgets = UserWidget::where('widget_id', 1)->get();
        foreach ($widgets as $widget) {
            $settings = $widget->settings;

            $settings['group'] = $settings['device_group'];
            unset($settings['device_group']);

            $widget->settings = $settings;
            $widget->save();
        }
    }
}
