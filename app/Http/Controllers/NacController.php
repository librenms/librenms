<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Util\Html;

class NacController extends Controller
{
    public function index()
    {
        $data = [];
        return view('nac', $data);
    }

}
