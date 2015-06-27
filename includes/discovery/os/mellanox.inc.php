<?php

if (!$os) {
    if (stristr($sysDescr, "mellanox")) {
        $os = "mellanox";
    }
}
