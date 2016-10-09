<?php
// PHP CS Fixer config file


$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('html/lib')
    ->exclude('lib')
    ->exclude('logs')
    ->exclude('mibs')
    ->exclude('rrd')
    ->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->finder($finder);
