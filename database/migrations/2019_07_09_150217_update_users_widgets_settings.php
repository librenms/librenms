<?php

use App\Models\UserWidget;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
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
    public function down(): void
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
};
