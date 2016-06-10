<?php
    if (strstr($sysDescr, 'Eaton 5P') ||
        strstr($sysDescr, 'Eaton 9130') ||
        strstr($sysDescr, 'Eaton 93E') ||
        strstr($sysDescr, 'Eaton 9PX') ||
        strstr($sysDescr, 'Eaton Evolution') ||
        strstr($sysDescr, 'Eaton EX') ) {

        $os = 'eatonups';
    }
