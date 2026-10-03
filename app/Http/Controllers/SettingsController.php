<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use App\View\SettingPresenter;
use Illuminate\Http\Request;
use LibreNMS\Util\DynamicConfig;
use LibreNMS\Util\DynamicConfigItem;

class SettingsController
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     *
     * @param  DynamicConfig  $dynamicConfig
     * @param  string  $tab
     * @param  string  $section
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(DynamicConfig $dynamicConfig, $tab = 'alerting', $section = '')
    {
        $this->authorize('settings.view');

        $items = $dynamicConfig->all()
            ->filter(fn (DynamicConfigItem $item) => $item->isValid() && $item->getGroup() && $item->getSection());

        $data = [
            'active_tab' => $tab,
            'active_section' => $section,
            'groups' => $this->buildGroups($items),
            'settings' => $items->map(fn (DynamicConfigItem $item) => SettingPresenter::present($item->toArray())),
        ];

        return view('settings.index', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  DynamicConfig  $config
     * @param  Request  $request
     * @param  string  $id
     * @return JsonResponse
     */
    public function update(DynamicConfig $config, Request $request, $id)
    {
        $this->authorize('settings.update');

        $value = $request->input('value');

        if (! $config->isValidSetting($id)) {
            return $this->jsonResponse($id, ':id is not a valid setting', null, 400);
        }

        $current = \App\Facades\LibrenmsConfig::get($id);
        $config_item = $config->get($id);

        if (! $config_item->checkValue($value)) {
            return $this->jsonResponse($id, $config_item->getValidationMessage($value), $current, 400);
        }

        if (\App\Facades\LibrenmsConfig::persist($id, $value)) {
            return $this->jsonResponse($id, "Successfully set $id", $value);
        }

        return $this->jsonResponse($id, 'Failed to update :id', $current, 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  DynamicConfig  $config
     * @param  string  $id
     * @return JsonResponse
     */
    public function destroy(DynamicConfig $config, $id)
    {
        $this->authorize('settings.update');

        if (! $config->isValidSetting($id)) {
            return $this->jsonResponse($id, ':id is not a valid setting', null, 400);
        }

        $dbConfig = \App\Models\Config::withChildren($id)->get();
        if ($dbConfig->isEmpty()) {
            return $this->jsonResponse($id, ':id is not set', $config->get($id)->default, 400);
        }

        $dbConfig->each->delete();

        return $this->jsonResponse($id, ':id reset to default', $config->get($id)->default);
    }

    /**
     * Build the sorted tab/section/setting structure
     *
     * @param  \Illuminate\Support\Collection<string, DynamicConfigItem>  $items
     * @return list<array{name: string, text: string, sections: list<array{name: string, text: string, description: ?string, settings: list<string>}>}>
     */
    private function buildGroups($items): array
    {
        return $items->groupBy(fn (DynamicConfigItem $item) => $item->getGroup())
            ->map(fn ($groupItems, $group) => [
                'name' => (string) $group,
                'text' => SettingPresenter::translateOrNull("settings.groups.$group") ?? (string) $group,
                'sections' => $groupItems->groupBy(fn (DynamicConfigItem $item) => $item->getSection())
                    ->map(fn ($sectionItems, $section) => [
                        'name' => (string) $section,
                        'text' => SettingPresenter::translateOrNull("settings.sections.$group.$section.name") ?? (string) $section,
                        'description' => SettingPresenter::translateOrNull("settings.sections.$group.$section.description"),
                        'settings' => $sectionItems->sortBy(fn (DynamicConfigItem $item) => $item['order'] ?? PHP_INT_MAX)
                            ->map(fn (DynamicConfigItem $item) => $item->getName())->values()->all(),
                    ])->sortBy('text', SORT_NATURAL | SORT_FLAG_CASE)->values()->all(),
            ])->sortBy('text', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
    }

    /**
     * @param  string  $id
     * @param  string  $message
     * @param  mixed  $value
     * @param  int  $status
     * @return JsonResponse
     */
    protected function jsonResponse($id, $message, $value = null, $status = 200)
    {
        return new JsonResponse([
            'message' => __($message, ['id' => $id]),
            'value' => $value,
        ], $status);
    }
}
