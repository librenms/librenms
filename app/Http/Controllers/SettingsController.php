<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LibreNMS\Util\DynamicConfig;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param string $tab
     * @param string $section
     * @return \Illuminate\Http\Response
     */
    public function index($tab = 'global', $section = '')
    {
        $data = [];
        $config = new DynamicConfig();

        $data['active_tab'] = $tab;
        $data['active_section'] = $section;
        $data['groups'] = $config->getGrouped();
        $data['tabs'] = $data['groups']->keys();
//        dd($data['groups']['alerting']['email']);

        return view('settings.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
