<?php

// do not move to yaml, this check needs to happen last
if (starts_with($sysDescr, 'FreeBSD')) {
    $os = 'freebsd';
}
