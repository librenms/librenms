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

	$data['locations'] = Location::orderBy('location')->get()->map(function ($location) {
 		/** @var Location $location */
		return [
			'id' => $location->id,
	        	'location' => $location->location,
                	'total' => $location->devices()->count(),
			'up' => $location->devices()->isUp()->count(),
			'down' => $location->devices()->isDown()->count(),
			'ignored' => $location->devices()->isIgnored()->count(),
			'disabled' => $location->devices()->isDisabled()->count(),
		];
	});



        return view('widgets.device-location', $data);
    }

}
