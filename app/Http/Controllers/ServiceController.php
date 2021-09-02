<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Toastr;

class ServiceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function store(Request $request)
    {
        $request = [
            'service_name' => 'required|string|unique:service',
            'device_id' => 'integer',
            'service_type' => 'string',
            'service_param' => 'nullable|string',
            'service_ip' => 'nullable|string',
            'service_desc' => 'nullable|string',
            'service_changed' => 'integer',
            'service_disabled' => 'integer',
            'service_ignore' => 'integer',
        ];

        $service = Service::make(
            $request->only(
                [
                    'service_name',
                    'device_id',
                    'service_type',
                    'service_param',
                    'service_ip',
                    'service_desc',
                    'service_changed',
                    'service_disabled',
                    'service_ignore',
                ]
            )
        );
        $service->save();

        Toastr::success(__('Service :name created', ['name' => $service->service_name]));

        return redirect()->route('services.templates.index');
    }
}
