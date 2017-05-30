<?php

require_once 'common.php';

test_notify(new \cli\progress\Bar('  \cli\progress\Bar displays a progress bar', 1000000));
test_notify(new \cli\progress\Bar('  It sizes itself dynamically', 1000000));
