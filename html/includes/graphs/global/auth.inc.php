<?php

use LibreNMS\Authentication\LegacyAuth;

if (LegacyAuth::user()->hasGlobalRead()) {
    $auth = 1;
}
