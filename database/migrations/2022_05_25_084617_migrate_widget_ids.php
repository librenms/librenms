<?php

use App\Models\UserWidget;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

class MigrateWidgetIds extends Migration
{
    /** @var Illuminate\Support\Collection<string, mixed> */
    private $map;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->map = DB::table('widgets')->pluck('widget', 'widget_id');

        UserWidget::query()->chunk(1000, function (Collection $widgets) {
            $widgets->each(function ($widget) {
                $widget->widget = $this->map[$widget->widget_id];
                $widget->save();
            });
        });

        Schema::table('users_widgets', function (Blueprint $table) {
            $table->string('widget', 32)->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_widgets', function (Blueprint $table) {
            $table->string('widget', 32)->default('')->change();
        });
    }
}
