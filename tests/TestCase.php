<?php

namespace LibreNMS\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use SnmpsimHelpers;
}
