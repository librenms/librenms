<?php

if (Auth::user()->hasGlobalRead()) {
    $auth = 1;
} elseif (Auth::user()->hasLimitedWrite()) {
    $auth = 1;
}
