<?php

// do not move to yaml, this check needs to happen last

if (starts_with($sysDescr, 'Linux') || starts_with($sysObjectId, '.1.3.6.1.4.1.8072.3.2.10')) {
    $os = 'linux';
}
