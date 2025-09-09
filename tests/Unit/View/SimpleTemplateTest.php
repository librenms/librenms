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

namespace LibreNMS\Tests\Unit\View;

use App\View\SimpleTemplate;
use PHPUnit\Framework\TestCase;

final class SimpleTemplateTest extends TestCase
{
    public function testBasicVariableReplacement(): void
    {
        $template = new SimpleTemplate('Hello {{ name }}!', ['name' => 'World']);
        $this->assertEquals('Hello World!', (string) $template);
    }

    public function testVariableReplacementWithDollarPrefix(): void
    {
        $template = new SimpleTemplate('Hello {{ $name }}!', ['name' => 'World']);
        $this->assertEquals('Hello World!', (string) $template);
    }

    public function testStaticParseMethod(): void
    {
        $result = SimpleTemplate::parse('Hello {{ name }}!', ['name' => 'World']);
        $this->assertEquals('Hello World!', $result);
    }

    public function testSetVariable(): void
    {
        $template = new SimpleTemplate('Hello {{ name }}!');
        $template->setVariable('name', 'World');
        $this->assertEquals('Hello World!', (string) $template);
    }

    public function testKeepEmptyTemplates(): void
    {
        $template = new SimpleTemplate('Hello {{ missing }}!');
        $this->assertEquals('Hello !', (string) $template);

        $template = new SimpleTemplate('Hello {{ missing }}!');
        $template->keepEmptyTemplates();
        $this->assertEquals('Hello {{ missing }}!', (string) $template);
    }

    public function testCustomCallback(): void
    {
        $template = new SimpleTemplate('Hello {{ name }}!');
        $template->replaceWith(function ($matches) {
            return strtoupper($matches[1]);
        });
        $this->assertEquals('Hello NAME!', (string) $template);
    }

    public function testTrimFilter(): void
    {
        $template = new SimpleTemplate('{{ value|trim }}', ['value' => '  Hello World  ']);
        $this->assertEquals('Hello World', (string) $template);
    }

    public function testUpperFilter(): void
    {
        $template = new SimpleTemplate('{{ value|upper }}', ['value' => 'hello']);
        $this->assertEquals('HELLO', (string) $template);
    }

    public function testLowerFilter(): void
    {
        $template = new SimpleTemplate('{{ value|lower }}', ['value' => 'HELLO']);
        $this->assertEquals('hello', (string) $template);
    }

    public function testTitleFilter(): void
    {
        $template = new SimpleTemplate('{{ value|title }}', ['value' => 'hello world']);
        $this->assertEquals('Hello World', (string) $template);
    }

    public function testCapitalizeFilter(): void
    {
        $template = new SimpleTemplate('{{ value|capitalize }}', ['value' => 'hello world']);
        $this->assertEquals('Hello world', (string) $template);
    }

    public function testLengthFilter(): void
    {
        $template = new SimpleTemplate('{{ value|length }}', ['value' => 'Hello']);
        $this->assertEquals('5', (string) $template);
    }

    public function testReplaceFilter(): void
    {
        $template = new SimpleTemplate('{{ value|replace("world", "universe") }}', ['value' => 'hello world']);
        $this->assertEquals('hello universe', (string) $template);
    }

    public function testReplaceFilterWithSingleQuotes(): void
    {
        $template = new SimpleTemplate("{{ value|replace('\"anon\" ', '') }}", ['value' => 'john "anon" doe']);
        $this->assertEquals('john doe', (string) $template);
    }

    public function testReplaceFilterReplacementWithSingleQuotes(): void
    {
        $template = new SimpleTemplate("{{ value|replace(\"ryan's\", 'hello') }}", ['value' => "ryan's world"]);
        $this->assertEquals('hello world', (string) $template);
    }

    public function testReplaceFilterWithNoQuotes(): void
    {
        $template = new SimpleTemplate('{{ value|replace(world, universe) }}', ['value' => 'hello world']);
        $this->assertEquals('hello universe', (string) $template);
    }

    public function testSliceFilter(): void
    {
        $template = new SimpleTemplate('{{ value|slice(0, 5) }}', ['value' => 'Hello World']);
        $this->assertEquals('Hello', (string) $template);
    }

    public function testSliceFilterWithoutLength(): void
    {
        $template = new SimpleTemplate('{{ value|slice(6) }}', ['value' => 'Hello World']);
        $this->assertEquals('World', (string) $template);
    }

    public function testEscapeFilter(): void
    {
        $template = new SimpleTemplate('{{ value|escape }}', ['value' => '<script>alert("xss")</script>']);
        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', (string) $template);
    }

    public function testEscapeFilterWithStrategy(): void
    {
        $template = new SimpleTemplate('{{ value|escape("html") }}', ['value' => '<div>test</div>']);
        $this->assertEquals('&lt;div&gt;test&lt;/div&gt;', (string) $template);
    }

    public function testUrlEncodeFilter(): void
    {
        $template = new SimpleTemplate('{{ value|url_encode }}', ['value' => 'hello world']);
        $this->assertEquals('hello+world', (string) $template);
    }

    public function testStriptagsFilter(): void
    {
        $template = new SimpleTemplate('{{ value|striptags }}', ['value' => '<p>Hello <b>World</b></p>']);
        $this->assertEquals('Hello World', (string) $template);
    }

    public function testNl2brFilter(): void
    {
        $template = new SimpleTemplate('{{ value|nl2br }}', ['value' => "Hello\nWorld"]);
        $this->assertEquals("Hello<br />\nWorld", (string) $template);
    }

    public function testRawFilter(): void
    {
        $template = new SimpleTemplate('{{ value|raw }}', ['value' => '<b>Hello</b>']);
        $this->assertEquals('<b>Hello</b>', (string) $template);
    }

    public function testNumberFormatFilter(): void
    {
        $template = new SimpleTemplate('{{ value|number_format(2) }}', ['value' => '1234.5678']);
        $this->assertEquals('1,234.57', (string) $template);
    }

    public function testNumberFormatFilterWithCustomSeparators(): void
    {
        $template = new SimpleTemplate('{{ value|number_format(2, ".", " ") }}', ['value' => '1234.5678']);
        $this->assertEquals('1 234.57', (string) $template);
    }

    public function testDateFilter(): void
    {
        $template = new SimpleTemplate('{{ value|date("Y-m-d") }}', ['value' => '2023-12-25 15:30:00']);
        $this->assertEquals('2023-12-25', (string) $template);
    }

    public function testDateFilterWithTimestamp(): void
    {
        $timestamp = mktime(15, 30, 0, 12, 25, 2023);
        $template = new SimpleTemplate('{{ value|date("Y-m-d H:i") }}', ['value' => (string) $timestamp]);
        $this->assertEquals('2023-12-25 15:30', (string) $template);
    }

    public function testJsonEncodeFilter(): void
    {
        $template = new SimpleTemplate('{{ value|json_encode }}', ['value' => 'Hello "World"']);
        $this->assertEquals('"Hello \"World\""', (string) $template);
    }

    public function testDefaultFilter(): void
    {
        $template = new SimpleTemplate('{{ value|default("fallback") }}', ['value' => '']);
        $this->assertEquals('fallback', (string) $template);
    }

    public function testDefaultFilterWithValue(): void
    {
        $template = new SimpleTemplate('{{ value|default("fallback") }}', ['value' => 'actual']);
        $this->assertEquals('actual', (string) $template);
    }

    public function testAbsFilter(): void
    {
        $template = new SimpleTemplate('{{ value|abs }}', ['value' => '-42']);
        $this->assertEquals('42', (string) $template);
    }

    public function testRoundFilter(): void
    {
        $template = new SimpleTemplate('{{ value|round(2) }}', ['value' => '3.14159']);
        $this->assertEquals('3.14', (string) $template);
    }

    public function testRoundFilterWithoutPrecision(): void
    {
        $template = new SimpleTemplate('{{ value|round }}', ['value' => '3.7']);
        $this->assertEquals('4', (string) $template);
    }

    public function testChainedFilters(): void
    {
        $template = new SimpleTemplate('{{ value|trim|upper }}', ['value' => '  hello  ']);
        $this->assertEquals('HELLO', (string) $template);
    }

    public function testComplexChainedFilters(): void
    {
        $template = new SimpleTemplate('{{ value|slice(0, 5)|upper }}', ['value' => 'hello world']);
        $this->assertEquals('HELLO', (string) $template);
    }

    public function testDotNotationVariables(): void
    {
        $template = new SimpleTemplate('{{ user.name }}', ['user.name' => 'John Doe']);
        $this->assertEquals('John Doe', (string) $template);
    }

    public function testUnknownFilter(): void
    {
        $template = new SimpleTemplate('{{ value|unknown_filter }}', ['value' => 'test']);
        $this->assertEquals('test', (string) $template);
    }

    public function testMultipleVariables(): void
    {
        $template = new SimpleTemplate('{{ greeting }} {{ name }}!', [
            'greeting' => 'Hello',
            'name' => 'World',
        ]);
        $this->assertEquals('Hello World!', (string) $template);
    }

    public function testComplexTemplate(): void
    {
        $template = new SimpleTemplate(
            'Welcome {{ name|title }}! You have {{ count|number_format }} {{ item|lower }}{{ count|default("1")|slice(-1)|escape("js") != "1" ? "s" : "" }}.',
            [
                'name' => 'john doe',
                'count' => '1234',
                'item' => 'MESSAGE',
            ]
        );

        // SimpleTemplate doesn't support ternary, so this will be failed a bit
        // $expected = 'Welcome John Doe! You have 1,234 messages.';
        $this->assertEquals('Welcome John Doe! You have 1,234 message"4".', (string) $template);

        $simpleTemplate = new SimpleTemplate('Welcome {{ name|title }}! You have {{ count|number_format }} {{ item|lower }}.', [
            'name' => 'john doe',
            'count' => '1234',
            'item' => 'MESSAGE',
        ]);
        $this->assertEquals('Welcome John Doe! You have 1,234 message.', (string) $simpleTemplate);
    }
}
