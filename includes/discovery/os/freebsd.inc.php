<?php

// do not move to yaml, this check needs to happen last
if (str_contains($sysDescr, 'FreeBSD')) {
    $os = 'freebsd';
}
