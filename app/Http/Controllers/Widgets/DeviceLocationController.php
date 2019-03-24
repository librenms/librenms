<?php

namespace App\Http\Controllers\Widgets;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Location;
use LibreNMS\Config;
use DB;
#use Illuminate\Support\Facades\DB;

class DeviceLocationController extends WidgetController
{
    protected $title = 'Device Location Summary';

    #public function getSettingView(Request $request)
    #{
    #
    #}

    public function getView(Request $request)
    {

        $data = $this->getSettings();

        $data['locations'] = DB::select("select 
            loc.id, 
            loc.location, 
            (SELECT count(*) FROM devices d WHERE d.location_id=loc.id) AS 'total', 
            (SELECT count(*) from devices d WHERE d.location_id=loc.id and d.`status`='1') AS 'up',
            (SELECT count(*) from devices d WHERE d.location_id=loc.id and d.`status`='0') AS 'down',
            (SELECT count(*) from devices d WHERE d.location_id=loc.id and d.`ignore`='1') AS 'ignore',
            (SELECT count(*) from devices d WHERE d.location_id=loc.id and d.`disabled`='1') AS 'disabled'
            FROM locations loc 
            ORDER BY loc.location ASC");

        $data["summary"] = DB::select("select 
            (SELECT count(*) FROM devices d) AS 'total', 
            (SELECT count(*) from devices d WHERE d.`status`='1') AS 'up',
            (SELECT count(*) from devices d WHERE d.`status`='0') AS 'down',
            (SELECT count(*) from devices d WHERE d.`ignore`='1') AS 'ignore',
            (SELECT count(*) from devices d WHERE d.`disabled`='1') AS 'disabled'");

        return view('widgets.device-location', $data);
    }

}
