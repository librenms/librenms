<?php

// handle OAuth requests
$request = request();  // grab the Request object

if ($request->has('oauthtransport')) {
    // make sure transport is safe
    $validator = Validator::make($request->all(), ['oauthtransport' => 'required|alpha']);

    if ($validator->passes()) {
        $transport_name = $request->get('oauthtransport');
        $class = 'LibreNMS\\Alert\\Transport\\' . $transport_name;
        if (class_exists($class)) {
            $transport = app($class);
            if ($transport->handleOauth($request)) {
                Toastr::success("$transport_name added successfully.");
            } else {
                Toastr::error("$transport_name was not added. Check the log for details.");
            }
        }
    }

    // remove get variables otherwise things will get double added
    echo '<script>window.history.replaceState(null, null, window.location.pathname);</script>';
}
unset($request);

// print alert transports
require_once 'includes/html/print-alert-transports.php';
