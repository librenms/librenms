<?php

if (Auth::user()->hasGlobalRead()) {
    $auth = 1;
}
