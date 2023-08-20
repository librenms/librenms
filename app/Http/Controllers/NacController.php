<?php

namespace App\Http\Controllers;

class NacController extends Controller
{
    public function index()
    {
        $data = [];

        return view('nac', $data);
    }
}
