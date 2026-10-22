<?php

use App\Facades\LibrenmsConfig;
use App\Models\Port;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

$customer_types = Arr::wrap(LibrenmsConfig::get('customers_descr', ['cust']));

// legacy links passed the customer name as id
$customer = (string) ($vars['customer'] ?? $vars['id'] ?? '');

$customer_ports = Port::with('device')
    ->whereHas('device')
    ->where('port_descr_descr', $customer)
    ->whereIn('port_descr_type', $customer_types)
    ->when(! $auth, fn ($query) => Auth::user() ? $query->hasAccess(Auth::user()) : $query->whereRaw('1 = 0'))
    ->get();

if ($auth || $customer_ports->isNotEmpty()) {
    $title = 'Customer :: ' . $customer;
    $auth = true;
}
