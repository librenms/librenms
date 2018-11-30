<?php

namespace LibreNMS\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class LaravelTestCase extends BaseTestCase
{
    use CreatesApplication;
    use SnmpsimHelpers;
}
