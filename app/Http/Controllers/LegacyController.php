<?php

namespace App\Http\Controllers;

use App\Checks;

class LegacyController extends Controller
{
    public function index($path = '')
    {
        Checks::postAuth();

        ob_start();
        include base_path('html/legacy_index.php');
        $html = ob_get_clean();

        return response($html);
    }

    public function api($path = '')
    {
        include base_path('html/legacy_api_v0.php');
    }

    public function dash()
    {
        ob_start();
        include base_path('html/legacy/ajax_dash.php');
        $output = ob_get_contents();
        ob_end_clean();

        return response($output, 200, ['Content-Type' => 'application/json']);
    }
}
