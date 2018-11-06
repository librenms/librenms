<?php

namespace LibreNMS\Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Laravel\Dusk\TestCase as BaseTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $capability = DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY,
            (new ChromeOptions)->addArguments([
                '--disable-gpu',
                '--headless'
            ])
        );

        return RemoteWebDriver::create('http://localhost:9515', $capability);
    }

}
