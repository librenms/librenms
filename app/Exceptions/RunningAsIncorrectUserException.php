<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ShouldntReport;
use Symfony\Component\Console\Exception\ExceptionInterface;

class RunningAsIncorrectUserException extends \Exception implements ExceptionInterface, ShouldntReport
{
    //
}
