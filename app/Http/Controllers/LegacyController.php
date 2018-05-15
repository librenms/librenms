<?php

namespace App\Http\Controllers;

class LegacyController extends Controller
{
    public function index($path = '')
    {
        ob_start();
        include base_path('html/legacy_index.php');
        $html = ob_get_clean();

        return response($html);
    }

    public function api($path = '')
    {
        include base_path('html/legacy_api_v0.php');
    }
}
