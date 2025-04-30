<?php
use LibreNMS\Tests\TestCase;

class LocationCoordinatesTest extends TestCase
{
    /**
     * Regexes
     */
    private array $regexes = [
        'brackets + comma' => '/\[\s*(?<lat>[-+]?(?:[1-8]?\d(?:\.\d+)?|90(?:\.0+)?))\s{0,1},\s{0,1}(?<lng>[-+]?(?:180(?:\.0+)?|(?:(?:1[0-7]\d)|(?:[1-9]?\d))(?:\.\d+)?))\s*\]/',
        'space' => '/^(?<lat>[-+]?(?:[1-8]?\d(?:\.\d+)?|90(?:\.0+)?))\s{1}(?<lng>[-+]?(?:180(?:\.0+)?|(?:(?:1[0-7]\d)|(?:[1-9]?\d))(?:\.\d+)?))$/',
        'comma' => '/^(?<lat>[-+]?(?:[1-8]?\d(?:\.\d+)?|90(?:\.0+)?))\s{0,1},\s{0,1}(?<lng>[-+]?(?:180(?:\.0+)?|(?:(?:1[0-7]\d)|(?:[1-9]?\d))(?:\.\d+)?))$/',
    ];

    /**
     * Test for hitting regex
     */
    private function matchesAnyRegex(string $input): bool
    {
        foreach ($this->regexes as $regex) {
            if (preg_match($regex, $input, $m)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Test valid coordinates
     */
    public function testValidCoordinates(): void
    {
        $validLocations = [
            'text [12.45,12.45]',
            'text [23.456 , 100.654]',
            '[23.456,100.654]',
            '[23.456 , 100.654]',
            '12.45 12.45',
            '12.45,12.45',
            '12.45 , 12.45',
        ];

        foreach ($validLocations as $location) {
            $this->assertTrue($this->matchesAnyRegex($location), "Expected valid: '$location'");
        }
    }

    /**
     * Test invalid coordinates
     */
    public function testInvalidCoordinates(): void
    {
        $invalidLocations = [
            'text [12.45,     12.45]',
            '12.45,   12.45',
            '[text]',
            '12.45   12.45',
            'text 12.45,12.45',
            'text 12.45 12.45',
            '12.45,,12.45',
            'text',
        ];

        foreach ($invalidLocations as $location) {
            $this->assertFalse($this->matchesAnyRegex($location), "Expected invalid: '$location'");
        }
    }
}
