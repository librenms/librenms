<?php

namespace App\Restify;

use Binaryk\LaravelRestify\Commands\PolicyCommand;

/**
 * Fix deprecation warning
 */
class RestifyPolicyCommand extends PolicyCommand
{
    protected $type = 'Policy';
}
