<?php

require_once 'common.php';

\cli\line("========\nDots\n");

test_notify(new \cli\notify\Dots('  \cli\notify\Dots cycles through a set number of dots'));
test_notify(new \cli\notify\Dots('  You can disable the delay if ticks take longer than a few milliseconds', 5, 0), 10, 100000);

\cli\line("\n========\nSpinner\n");

test_notify(new \cli\notify\Spinner('  \cli\notify\Spinner cycles through a set of characters to emulate a spinner'));
