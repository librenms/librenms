<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use App\Models\UserWidget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DashboardWidgetController extends Controller
{
    public function add(Dashboard $dashboard, Request $request): JsonResponse
    {
        $this->authorize('update', $dashboard);

        $this->validate($request, [
            'widget_type' => Rule::in(DashboardController::$widgets),
        ]);

        $type = $request->get('widget_type');
        $widget = new UserWidget([
            'user_id' => Auth::id(),
            'widget' => $type,
            'col' => 1,
            'row' => 1,
            'size_x' => 6,
            'size_y' => 3,
            'title' => trans("widgets.$type.title"),
            'refresh' => 60,
            'settings' => '',
        ]);
        $dashboard->widgets()->save($widget);

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'Widget ' . htmlentities($widget->title) . ' added',
            'extra' => $widget->attributesToArray(),
        ]);
    }

    public function remove(UserWidget $widget): JsonResponse
    {
        $this->authorize('update', $widget->dashboard);

        $widget->delete();

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'Widget ' . htmlentities($widget->title) . ' removed',
        ]);
    }

    public function clear(Dashboard $dashboard): JsonResponse
    {
        $this->authorize('update', $dashboard);

        $dashboard->widgets()->delete();

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'All widgets removed',
        ]);
    }

    public function update(Dashboard $dashboard, Request $request): JsonResponse
    {
        $this->authorize('update', $dashboard);

        $validated = $this->getValidationFactory()->make(
            json_decode($request->get('data', '[]'), true), [
                '*' => 'array',
                '*.id' => 'integer',
                '*.col' => 'integer',
                '*.row' => 'integer',
                '*.size_x' => 'integer',
                '*.size_y' => 'integer',
            ])->validate();

        foreach ($validated as $item) {
            if ($widget = UserWidget::find($item['id'])) {
                $widget->fill($item);
                $widget->save();
            }
        }

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'Widgets updated',
        ]);
    }
}
