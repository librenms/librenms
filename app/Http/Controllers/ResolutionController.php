<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResolutionController extends Controller
{
    public function set(Request $request)
    {
        $this->validate($request, [
            'width' => 'required|numeric',
            'height' => 'required|numeric'
        ]);

        // legacy session
        session_start();
        $_SESSION['screen_width'] = $request->width;
        $_SESSION['screen_height'] = $request->height;
        session_write_close();

        // laravel session
        session([
            'screen_width' => $request->width,
            'screen_height' => $request->height
        ]);

        return $request->width . 'x' . $request->height;
    }
}
