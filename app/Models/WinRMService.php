<?php

namespace App\Models;


abstract class WinRMService extends BasicEnum
{
    public const 0 = "Running";
    public const 1 = "Paused";
    public const 2 = "Starting";
    public const 3 = "Pausing";
    public const 4 = "Starting after pause";
    public const 5 = "Stopping";
    public const 6 = "Stopped";
    public const 255 = "unable to get current service state";

}

