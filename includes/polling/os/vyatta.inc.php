<?php

list($features, $version) = explode('-', trim(str_replace('Vyatta', '', $poll_device['sysDescr'])), 2);
