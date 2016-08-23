<?php
/**
 * Created by PhpStorm.
 * User: crc
 * Date: 8/23/16
 * Time: 12:39 PM
 */

if (!$os) {
    if (strstr($sysObjectId, '.1.3.6.1.4.1.2879.1.1.2')) {
        $os = 'sonus-gsx';
    }
}