<?php
// PHP CS Fixer config file


$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('html/includes/geshi')
    ->exclude('html/includes/jpgraph')
    ->exclude('html/includes/Slim')
    ->exclude('html/lib')
    ->exclude('lib')
    ->exclude('logs')
    ->exclude('mibs')
    ->exclude('rrd')
    ->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->finder($finder);
