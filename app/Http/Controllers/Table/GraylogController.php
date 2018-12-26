<?php
/**
 * GraylogController.php
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

namespace App\Http\Controllers\Table;

use App\ApiClients\GraylogApi;
use App\Models\Device;
use DateInterval;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class GraylogController extends SimpleTableController
{
    private $timezone;

    public function __construct()
    {
        $timezone = Config::get('graylog.timezone');
        $this->timezone = $timezone ? new DateTimeZone($timezone) : null;
    }

    public function __invoke(Request $request, GraylogApi $api)
    {
        if (!$api->isConfigured()) {
            return response()->json([
                'error' => 'Graylog is not configured',
            ], 503);
        }

        $this->validate($request, [
            'stream' => 'nullable|alpha_num',
            'device' => 'nullable|int',
            'range' => 'nullable|int',
        ]);

        $search = $request->get('searchPhrase');
        $device_id = $request->get('device');
        $device = $device_id ? Device::find($device_id) : null;
        $range = $request->get('range', 0) ;
        $limit = $request->get('rowCount', 10);
        $page = $request->get('current', 1);
        $offset = ($page - 1) * $limit;
        $query = $api->buildSimpleQuery($search, $device);

        $sort = null;
        foreach ($request->get('sort', []) as $field => $direction) {
            $sort = "$field:$direction";
        }

        $stream = $request->get('stream');
        $filter = $stream ? "streams:$stream" : null;

        try {
            $data = $api->query($query, $range, $limit, $offset, $sort, $filter);
            return $this->formatResponse(
                array_map([$this, 'formatMessage'], $data['messages']),
                $page,
                count($data['messages']),
                $data['total_results']
            );
        } catch (\Exception $se) {
            $error = $se->getMessage();
        }

        return response()->json([
            'error' => $error,
        ], 500);
    }

    private function formatMessage($message)
    {
        if ($this->timezone) {
            $graylogTime = new DateTime($message['message']['timestamp']);
            $offset = $this->timezone->getOffset($graylogTime);

            $timeInterval = DateInterval::createFromDateString((string)$offset . 'seconds');
            $graylogTime->add($timeInterval);
            $displayTime = $graylogTime->format('Y-m-d H:i:s');
        } else {
            $displayTime = $message['message']['timestamp'];
        }

        return [
            'severity'  => $this->severityLabel($message['message']['level']),
            'timestamp' => $displayTime,
            'source'    => '<a href="'.Url::generate(['page'=>'device', 'device'=>$message['message']['source']]).'">'.$message['message']['source'].'</a>',
            'message'   => $message['message']['message'],
            'facility'  => isset($message['message']['facility']) ? $message['message']['facility'] : '',
            'level'     => $message['message']['level'],
        ];
    }

    private function severityLabel($severity)
    {
        $map = [
            "0" => "label-danger",
            "1" => "label-danger",
            "2" => "label-danger",
            "3" => "label-danger",
            "4" => "label-warning",
            "5" => "label-info",
            "6" => "label-info",
            "7" => "label-default",
            ""  => "label-info",
        ];
        $barColor = isset($map[$severity]) ? $map[$severity] : 'label-info';
        return '<span class="alert-status '.$barColor .'" style="margin-right:8px;float:left;"></span>';
    }
}
