<?php
if (strpos($device['sysDescr'], 'Enterprise')) {
    list(,,$hardware,$version) = explode(' ', $device['sysDescr']);
} else {
    list(,$hardware,$version) = explode(' ', $device['sysDescr']);
}
