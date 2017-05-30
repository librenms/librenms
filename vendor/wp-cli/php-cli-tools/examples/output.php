<?php

require_once 'common.php';

\cli\out("  \\cli\\out sends output to STDOUT\n");
\cli\out("  It does not automatically append a new line\n");
\cli\out("  It does accept any number of %s which are then %s to %s for formatting\n", 'arguments', 'passed', 'sprintf');
\cli\out("  Alternatively, {:a} can use an {:b} as the second argument.\n\n", array('a' => 'you', 'b' => 'array'));

\cli\err('  \cli\err sends output to STDERR');
\cli\err('  It does automatically append a new line');
\cli\err('  It does accept any number of %s which are then %s to %s for formatting', 'arguments', 'passed', 'sprintf');
\cli\err("  Alternatively, {:a} can use an {:b} as the second argument.\n", array('a' => 'you', 'b' => 'array'));

\cli\line('  \cli\line forwards to \cli\out for output');
\cli\line('  It does automatically append a new line');
\cli\line('  It does accept any number of %s which are then %s to %s for formatting', 'arguments', 'passed', 'sprintf');
\cli\line("  Alternatively, {:a} can use an {:b} as the second argument.\n", array('a' => 'you', 'b' => 'array'));
