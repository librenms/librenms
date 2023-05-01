<?php

\LibreNMS\OS\VmwareEsxi::make($device)
    ->discoverVmInfo(DeviceCache::get($device['device_id']));
