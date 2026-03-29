<?php

namespace App\Http\Controllers;

use App\Checks;
use App\Facades\LibrenmsConfig;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Util\Debug;

class LegacyController extends Controller
{
    public function index(Request $request, Session $session)
    {
        Checks::postAuth();

        // Set variables
        $no_refresh = false; // may be overridden by included pages
        $init_modules = ['web', 'auth'];
        require base_path('/includes/init.php');

        Debug::set(Str::contains($request->path(), 'debug'));

        ob_start(); // protect against bad plugins that output during start
        \LibreNMS\Plugins::start();
        ob_end_clean();

        if (Str::contains($request->path(), 'widescreen=yes')) {
            $session->put('widescreen', 1);
        }
        if (Str::contains($request->path(), 'widescreen=no')) {
            $session->forget('widescreen');
        }

        // Load the settings for Multi-Tenancy.
        if (LibrenmsConfig::has('branding') && is_array(LibrenmsConfig::get('branding'))) {
            $branding = Arr::dot(LibrenmsConfig::get('branding.' . $request->server('SERVER_NAME'), LibrenmsConfig::get('branding.default')));
            foreach ($branding as $key => $value) {
                LibrenmsConfig::set($key, $value);
            }
        }

        // page_title_prefix is displayed, unless page_title is set FIXME: NEEDED?
        if (LibrenmsConfig::has('page_title')) {
            LibrenmsConfig::set('page_title_prefix', LibrenmsConfig::get('page_title'));
        }

        // render page
        ob_start();
        $vars['page'] = basename($vars['page'] ?? '');
        if ($vars['page'] && is_file('includes/html/pages/' . $vars['page'] . '.inc.php')) {
            require 'includes/html/pages/' . $vars['page'] . '.inc.php';
        } else {
            abort(404);
        }

        $html = ob_get_clean();
        ob_end_clean();

        if (isset($pagetitle) && is_array($pagetitle)) {
            // if prefix is set, put it in front
            if (LibrenmsConfig::get('page_title_prefix')) {
                array_unshift($pagetitle, LibrenmsConfig::get('page_title_prefix'));
            }

            // if suffix is set, put it in the back
            if (LibrenmsConfig::get('page_title_suffix')) {
                $pagetitle[] = LibrenmsConfig::get('page_title_suffix');
            }

            // create and set the title
            $title = implode(' - ', $pagetitle);
            $html .= "<script type=\"text/javascript\">\ndocument.title = '$title';\n</script>";
        }

        return response()->view('layouts.legacy_page', [
            'content' => $html,
            'refresh' => $no_refresh ? 0 : LibrenmsConfig::get('page_refresh'), // @phpstan-ignore ternary.alwaysFalse ($no_refresh may be set by included pages)
        ]);
    }

    public function dummy()
    {
        return 'Dummy page';
    }
}
