<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LibreNMS\Util\DynamicConfig;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param DynamicConfig $dynamicConfig
     * @param string $tab
     * @param string $section
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(DynamicConfig $dynamicConfig, $tab = 'global', $section = '')
    {
        $data = [
            'active_tab' => $tab,
            'active_section' => $section,
            'groups' => $dynamicConfig->getGroups()->reject(function ($group) {
                return $group == 'global';
            })->values(),
        ];

        return view('settings.index', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DynamicConfig $config
     * @param  \Illuminate\Http\Request $request
     * @param  string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DynamicConfig $config, Request $request, $id)
    {
        $value = $request->get('value');

        if (! $config->isValidSetting($id)) {
            return $this->jsonResponse($id, ':id is not a valid setting', null, 400);
        }

        $current = \LibreNMS\Config::get($id);
        $config_item = $config->get($id);

        if (! $config_item->checkValue($value)) {
            return $this->jsonResponse($id, $config_item->getValidationMessage($value), $current, 400);
        }

        if (\LibreNMS\Config::persist($id, $value)) {
            return $this->jsonResponse($id, "Successfully set $id", $value);
        }

        return $this->jsonResponse($id, 'Failed to update :id', $current, 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DynamicConfig $config
     * @param  string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(DynamicConfig $config, $id)
    {
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
     * List all settings (excluding hidden ones and ones that don't have metadata)
     *
     * @param DynamicConfig $config
     * @return JsonResponse
     */
    public function listAll(DynamicConfig $config)
    {
        return response()->json($config->all()->filter->isValid());
    }

    /**
     * @param string $id
     * @param string $message
     * @param mixed $value
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse($id, $message, $value = null, $status = 200)
    {
        return new JsonResponse([
            'message' => __($message, ['id' => $id]),
            'value' => $value,
        ], $status);
    }
}
