<?php

/**
 * Supported Devides List
 * OLT AN5516-06
 * OLT AN5516-01
 */
if (preg_match('/^AN5516-0[16]$/', $sysDescr)) {
        $os = "fiberhome";
}
