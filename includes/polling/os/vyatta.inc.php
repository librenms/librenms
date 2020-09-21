<?php

[$features, $version] = explode('-', trim(str_replace('Vyatta', '', $device['sysDescr'])), 2);
