<?php

/**
 * SimpleTemplateTest.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit;

use App\View\SimpleTemplate;
use PHPUnit\Framework\TestCase;

class SimpleTemplateTest extends TestCase
{
    public function testBasicSubstitution()
    {
        $template = new SimpleTemplate('{{ $greeting }}, {{ name }}!', ['name' => 'Tony', 'greeting' => 'Hello']);
        $this->assertSame('Hello, Tony!', (string) $template);
    }

    public function testMissingVariableRemovesPlaceholder()
    {
        $template = new SimpleTemplate('Hello, {{ unknown }}!', []);
        $this->assertSame('Hello, !', (string) $template);
    }

    public function testKeepEmptyTemplates()
    {
        $template = (new SimpleTemplate('Hello, {{ missing }}!'))->keepEmptyTemplates();
        $this->assertSame('Hello, {{ missing }}!', (string) $template);
    }

    public function testSetVariable()
    {
        $template = (new SimpleTemplate('Age: {{ age }}'))
            ->setVariable('age', '42');
        $this->assertSame('Age: 42', (string) $template);
    }

    public function testParseStaticMethod()
    {
        $result = SimpleTemplate::parse('Hello, {{ name }}!', ['name' => 'Tony']);
        $this->assertSame('Hello, Tony!', $result);
    }

    public function testCallbackReplacement()
    {
        $template = (new SimpleTemplate('User: {{ name }}'))->replaceWith(function ($matches) {
            return strtoupper($matches[1]);
        });

        $this->assertSame('User: NAME', (string) $template);
    }

    public function testSingleFilter()
    {
        $template = new SimpleTemplate('Lowercase: {{ name|upper }}', ['name' => 'tony']);
        $this->assertSame('Lowercase: TONY', (string) $template);
    }

    public function testChainedFilters()
    {
        $template = new SimpleTemplate('Slug: {{ title|lower|slug }}', ['title' => 'Hello World!']);
        $this->assertSame('Slug: hello-world', (string) $template);
    }

    public function testFilterWithArguments()
    {
        $template = new SimpleTemplate('Truncated: {{ text|truncate(5,"...") }}', ['text' => 'abcdefg']);
        $this->assertSame('Truncated: abcde...', (string) $template);
    }

    public function testDateFilter()
    {
        $timestamp = strtotime('2020-01-01 15:00');
        $template = new SimpleTemplate('Date: {{ time|date("Y") }}', ['time' => (string) $timestamp]);
        $this->assertSame('Date: 2020', (string) $template);
    }

    public function testDefaultFilter()
    {
        $template = new SimpleTemplate('Name: {{ name|default("Unknown") }}', []);
        $this->assertSame('Name: Unknown', (string) $template);
    }

    public function testNestedParenthesesInFilterArgs()
    {
        $template = new SimpleTemplate('Value: {{ val|replace("a,b","x") }}', ['val' => 'a,b,c']);
        $this->assertSame('Value: x,c', (string) $template);
    }

    public function testBase64Filters()
    {
        $template = new SimpleTemplate('Encoded: {{ val|base64_encode|base64_decode }}', ['val' => 'data']);
        $this->assertSame('Encoded: data', (string) $template);
    }

    public function testJsonEncodeFilter()
    {
        $template = new SimpleTemplate('JSON: {{ val|json_encode }}', ['val' => 'value']);
        $this->assertSame('JSON: "value"', (string) $template);
    }

    public function testReplaceFilterEdgeCases()
    {
        // Basic replacement
        $template = new SimpleTemplate('Out: {{ val|replace("a","x") }}', ['val' => 'banana']);
        $this->assertSame('Out: bxnxnx', (string) $template);

        // Unquoted arguments (should still work)
        $template = new SimpleTemplate('Out: {{ val|replace(a,x) }}', ['val' => 'banana']);
        $this->assertSame('Out: bxnxnx', (string) $template);

        // Comma inside string argument
        $template = new SimpleTemplate('Out: {{ val|replace("a,b","_") }}', ['val' => 'a,b,c']);
        $this->assertSame('Out: _,c', (string) $template);

        // Comma inside both args
        $template = new SimpleTemplate('Out: {{ val|replace("a,b","x,y") }}', ['val' => 'a,b,c']);
        $this->assertSame('Out: x,y,c', (string) $template);

        // Replace with empty string
        $template = new SimpleTemplate('Out: {{ val|replace("a","") }}', ['val' => 'banana']);
        $this->assertSame('Out: bnn', (string) $template);

        // Replace with space
        $template = new SimpleTemplate('Out: {{ val|replace("-"," ") }}', ['val' => '2024-01-01']);
        $this->assertSame('Out: 2024 01 01', (string) $template);

        // Multibyte characters
        $template = new SimpleTemplate('Out: {{ val|replace("é","e") }}', ['val' => 'café']);
        $this->assertSame('Out: cafe', (string) $template);

        // Replace with symbol
        $template = new SimpleTemplate('Out: {{ val|replace("a","@") }}', ['val' => 'data']);
        $this->assertSame('Out: d@t@', (string) $template);

        // Missing second argument: should return input unchanged
        $template = new SimpleTemplate('Out: {{ val|replace("a") }}', ['val' => 'banana']);
        $this->assertSame('Out: banana', (string) $template);

        // Extra arguments: should ignore extras
        $template = new SimpleTemplate('Out: {{ val|replace("a","x","ignored") }}', ['val' => 'banana']);
        $this->assertSame('Out: bxnxnx', (string) $template);

        // Empty input string
        $template = new SimpleTemplate('Out: {{ val|replace("a","x") }}', ['val' => '']);
        $this->assertSame('Out: ', (string) $template);

        // Placeholder not in string
        $template = new SimpleTemplate('Out: {{ val|replace("z","x") }}', ['val' => 'banana']);
        $this->assertSame('Out: banana', (string) $template);
    }

    public function testReplaceAndTrimChainedFilters()
    {
        $template = new SimpleTemplate('Out: {{ val|replace("_"," ")|trim }}', ['val' => '_test _']);
        $this->assertSame('Out: test', (string) $template);
    }
}
