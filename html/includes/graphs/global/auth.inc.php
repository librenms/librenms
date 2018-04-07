<?php

use LibreNMS\Authentication\Auth;

if (Auth::user()->hasGlobalRead()) {
    $auth = 1;
}
