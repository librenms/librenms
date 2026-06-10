<?php

$smokeping = new \LibreNMS\Util\Smokeping(DeviceCache::getPrimary());
$smokeping_files = $smokeping->findFiles();
