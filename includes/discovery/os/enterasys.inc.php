<?php

if (starts_with($sysDescr, 'Enterasys Networks')) {
    $os = 'enterasys';
} elseif (starts_with($sysObjectId, '.1.3.6.1.4.1.5624.2.1')) {
    $os = 'enterasys';
}
