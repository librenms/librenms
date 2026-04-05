<?php

/**
 * AlertOperationsController.php
 *
 * Web UI for reusable alert operations (global alert_operations + segments).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @link       https://www.librenms.org
 */

namespace App\Http\Controllers;

use App\Models\AlertOperation;
use Illuminate\Contracts\View\View;

class AlertOperationsController extends Controller
{
    /**
     * List alert operations (named operations + segment summary).
     *
     * Route: {@see routes/web.php} <code>can:admin</code> (same as alert-operation JSON API).
     */
    public function index(): View
    {
        $operations = AlertOperation::query()
            ->withCount('alertRules')
            ->with([
                'segments.transportSingles:alert_transports.transport_id,transport_type,transport_name',
                'segments.transportGroups:alert_transport_groups.transport_group_id,transport_group_name',
            ])
            ->orderBy('name')
            ->get();

        return view('alert.operations.index', [
            'operations' => $operations,
        ]);
    }
}
