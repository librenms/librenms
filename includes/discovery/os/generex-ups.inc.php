<?php

if (str_contains($sysDescr, array('CS121', 'CS141')) && str_contains($sysObjectId, '.1.3.6.1.2.1.33')) {
    $os = 'generex-ups';
}
