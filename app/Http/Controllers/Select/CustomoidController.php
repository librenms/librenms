<?php

namespace App\Http\Controllers\Select;

use App\Models\Customoid;

class CustomoidController extends SelectController
{
    /**
     * Defines the base query for this resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        return Customoid::hasAccess($request->user())
            ->with(['device' => function ($query) : void {
                $query->select('device_id', 'hostname', 'sysName', 'display');
            }])
            ->select('customoid_id', 'customoid_descr', 'device_id');
    }

    /**
     * @param  Customoid  $customoid
     */
    public function formatItem($customoid)
    {
        return [
            'id' => $customoid->customoid_id,
            'text' => $customoid->device->shortDisplayName() . ' (' . $customoid->customoid_descr . ')',
        ];
    }
}
