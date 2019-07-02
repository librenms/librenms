<?php
/**
 * WidgetController.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use App\Http\Controllers\Controller;
use App\Models\DeviceGroup;
use App\Models\UserWidget;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class WidgetController extends Controller
{
    /** @var string sets the title for this widget, use title() function if you need to dynamically generate */
    protected $title = 'Widget';

    /** @var array Set default values for settings */
    protected $defaults = [];

    private $show_settings = false;
    protected $settings = null;

    /**
     * @param Request $request
     * @return View
     */
    abstract public function getView(Request $request);

    /**
     * @param Request $request
     * @return View
     */
    public function getSettingsView(Request $request)
    {
        return view('widgets.settings.base');
    }

    public function __invoke(Request $request)
    {
        $this->show_settings = (bool)$request->get('settings');

        if ($this->show_settings) {
            $view = $this->getSettingsView($request);
        } else {
            $view = $this->getView($request);
        }

        $settings = $this->getSettings();
        if (!empty($settings['title'])) {
            $title = $settings['title'];
        } else {
            $title = __(method_exists($this, 'title') ? app()->call([$this, 'title']) : $this->title);
        }

        return $this->formatResponse($view, $title, $settings);
    }

    /**
     * Get the settings (with defaults applied)
     *
     * @return array
     */
    public function getSettings($settingsView = false)
    {
        if (is_null($this->settings)) {
            $id = \Request::get('id');
            $widget = UserWidget::find($id);
            $this->settings = array_replace($this->defaults, $widget ? (array)$widget->settings : []);
            $this->settings['id'] = $id;

            if ($settingsView && isset($this->settings['device_group'])) {
                $this->settings['device_group'] = DeviceGroup::find($this->settings['device_group']);
            }
        }

        return $this->settings;
    }

    /**
     * @param View|string $view
     * @param string $title
     * @param array $settings
     * @param string $status
     * @return \Illuminate\Http\JsonResponse
     */
    private function formatResponse($view, $title, $settings, $status = 'ok')
    {
        if ($view instanceof View) {
            $html = $view->__toString();
            $show_settings = (int)starts_with($view->getName(), 'widgets.settings.');
        } else {
            $html = (string)$view;
            $show_settings = (int)$this->show_settings;
        }

        return response()->json([
            'status' => $status,
            'title' => __($title),
            'html' => $html,
            'show_settings' => $show_settings,
            'settings' => $settings,
        ]);
    }
}
