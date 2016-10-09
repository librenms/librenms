<?php

if (preg_match('/AXIS .* (Network Camera|Video Server|Network Video Encoder)/', $sysDescr)) {
    $os = 'axiscam';
}
