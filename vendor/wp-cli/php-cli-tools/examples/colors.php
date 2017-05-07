<?php
// Samples. Lines marked with * should be colored in output
// php examples/colors.php
// *  All output is run through \cli\Colors::colorize before display
// *  All output is run through \cli\Colors::colorize before display
// *  All output is run through \cli\Colors::colorize before display
// *  All output is run through \cli\Colors::colorize before display
//    All output is run through \cli\Colors::colorize before display
// *  All output is run through \cli\Colors::colorize before display
// php examples/colors.php | cat
//    All output is run through \cli\Colors::colorize before display
// *  All output is run through \cli\Colors::colorize before display
//    All output is run through \cli\Colors::colorize before display
// *  All output is run through \cli\Colors::colorize before display
//    All output is run through \cli\Colors::colorize before display
//    All output is run through \cli\Colors::colorize before display

require_once 'common.php';

\cli\line('  %C%5All output is run through %Y%6\cli\Colors::colorize%C%5 before display%n');

echo \cli\Colors::colorize('  %C%5All output is run through %Y%6\cli\Colors::colorize%C%5 before display%n', true) . "\n";
echo \cli\Colors::colorize('  %C%5All output is run through %Y%6\cli\Colors::colorize%C%5 before display%n') . "\n";

\cli\Colors::enable(); // Forcefully enable
\cli\line('  %C%5All output is run through %Y%6\cli\Colors::colorize%C%5 before display%n');

\cli\Colors::disable(); // Disable forcefully!
\cli\line('  %C%5All output is run through %Y%6\cli\Colors::colorize%C%5 before display%n', true);
\cli\Colors::enable(false); // Enable, but not forcefully
\cli\line('  %C%5All output is run through %Y%6\cli\Colors::colorize%C%5 before display%n');

