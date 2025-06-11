<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Util\Compare;
use PHPUnit\Framework\TestCase;

class CompareTest extends TestCase
{
    /**
     * Test basic equality comparison (=)
     */
    public function testEqualityComparison()
    {
        // Basic equality
        $this->assertTrue(Compare::values(5, 5, '='));
        $this->assertTrue(Compare::values('hello', 'hello', '='));
        $this->assertTrue(Compare::values(true, true, '='));
        $this->assertFalse(Compare::values(5, 6, '='));

        // Type coercion (loose equality)
        $this->assertTrue(Compare::values('5', 5, '='));
        $this->assertTrue(Compare::values(1, true, '='));
        $this->assertTrue(Compare::values(0, false, '='));
        $this->assertTrue(Compare::values('', false, '='));

        // Numeric string comparison
        $this->assertTrue(Compare::values('10', '10.0', '='));
        $this->assertTrue(Compare::values('0', '0.0', '='));
    }

    /**
     * Test inequality comparison (!=)
     */
    public function testInequalityComparison()
    {
        $this->assertTrue(Compare::values(5, 6, '!='));
        $this->assertTrue(Compare::values('hello', 'world', '!='));
        $this->assertFalse(Compare::values(5, 5, '!='));
        $this->assertFalse(Compare::values('5', 5, '!=')); // loose comparison
    }

    /**
     * Test strict equality comparison (==)
     */
    public function testStrictEqualityComparison()
    {
        $this->assertTrue(Compare::values(5, 5, '=='));
        $this->assertTrue(Compare::values('hello', 'hello', '=='));
        $this->assertFalse(Compare::values('5', 5, '==')); // strict comparison
        $this->assertFalse(Compare::values(1, true, '=='));
        $this->assertFalse(Compare::values(0, false, '=='));
        $this->assertFalse(Compare::values('', false, '=='));
    }

    /**
     * Test strict inequality comparison (!==)
     */
    public function testStrictInequalityComparison()
    {
        $this->assertTrue(Compare::values('5', 5, '!=='));
        $this->assertTrue(Compare::values(1, true, '!=='));
        $this->assertTrue(Compare::values(0, false, '!=='));
        $this->assertFalse(Compare::values(5, 5, '!=='));
        $this->assertFalse(Compare::values('hello', 'hello', '!=='));
    }

    /**
     * Test greater than or equal comparison (>=)
     */
    public function testGreaterThanOrEqualComparison()
    {
        $this->assertTrue(Compare::values(10, 5, '>='));
        $this->assertTrue(Compare::values(5, 5, '>='));
        $this->assertFalse(Compare::values(3, 5, '>='));

        // String comparison
        $this->assertTrue(Compare::values('b', 'a', '>='));
        $this->assertTrue(Compare::values('a', 'a', '>='));
        $this->assertFalse(Compare::values('a', 'b', '>='));

        // Numeric strings
        $this->assertTrue(Compare::values('15', '10', '>='));
        $this->assertTrue(Compare::values('10.5', '10', '>='));
    }

    /**
     * Test less than or equal comparison (<=)
     */
    public function testLessThanOrEqualComparison()
    {
        $this->assertTrue(Compare::values(3, 5, '<='));
        $this->assertTrue(Compare::values(5, 5, '<='));
        $this->assertFalse(Compare::values(10, 5, '<='));

        // String comparison
        $this->assertTrue(Compare::values('a', 'b', '<='));
        $this->assertTrue(Compare::values('a', 'a', '<='));
        $this->assertFalse(Compare::values('b', 'a', '<='));
    }

    /**
     * Test greater than comparison (>)
     */
    public function testGreaterThanComparison()
    {
        $this->assertTrue(Compare::values(10, 5, '>'));
        $this->assertFalse(Compare::values(5, 5, '>'));
        $this->assertFalse(Compare::values(3, 5, '>'));

        // Floating point numbers
        $this->assertTrue(Compare::values(10.1, 10, '>'));
        $this->assertTrue(Compare::values(-5, -10, '>'));
    }

    /**
     * Test less than comparison (<)
     */
    public function testLessThanComparison()
    {
        $this->assertTrue(Compare::values(3, 5, '<'));
        $this->assertFalse(Compare::values(5, 5, '<'));
        $this->assertFalse(Compare::values(10, 5, '<'));

        // Negative numbers
        $this->assertTrue(Compare::values(-10, -5, '<'));
        $this->assertTrue(Compare::values(-10, 0, '<'));
    }

    /**
     * Test contains comparison
     */
    public function testContainsComparison()
    {
        $this->assertTrue(Compare::values('hello world', 'world', 'contains'));
        $this->assertTrue(Compare::values('hello world', 'hello', 'contains'));
        $this->assertTrue(Compare::values('hello world', 'o w', 'contains'));
        $this->assertFalse(Compare::values('hello world', 'xyz', 'contains'));

        // Case sensitive
        $this->assertFalse(Compare::values('Hello World', 'hello', 'contains'));

        // Empty string
        $this->assertTrue(Compare::values('hello', '', 'contains'));

        // Numeric values converted to string
        $this->assertTrue(Compare::values('12345', 23, 'contains'));
        $this->assertTrue(Compare::values(12345, '23', 'contains'));
    }

    /**
     * Test not_contains comparison
     */
    public function testNotContainsComparison()
    {
        $this->assertFalse(Compare::values('hello world', 'world', 'not_contains'));
        $this->assertTrue(Compare::values('hello world', 'xyz', 'not_contains'));
        $this->assertTrue(Compare::values('Hello World', 'hello', 'not_contains')); // case sensitive
        $this->assertFalse(Compare::values('hello', '', 'not_contains')); // empty string always contained
    }

    /**
     * Test starts comparison
     */
    public function testStartsComparison()
    {
        $this->assertTrue(Compare::values('hello world', 'hello', 'starts'));
        $this->assertTrue(Compare::values('hello world', 'h', 'starts'));
        $this->assertFalse(Compare::values('hello world', 'world', 'starts'));
        $this->assertFalse(Compare::values('hello world', 'Hello', 'starts')); // case sensitive

        // Empty string
        $this->assertTrue(Compare::values('hello', '', 'starts'));

        // Full match
        $this->assertTrue(Compare::values('hello', 'hello', 'starts'));
    }

    /**
     * Test not_starts comparison
     */
    public function testNotStartsComparison()
    {
        $this->assertFalse(Compare::values('hello world', 'hello', 'not_starts'));
        $this->assertTrue(Compare::values('hello world', 'world', 'not_starts'));
        $this->assertTrue(Compare::values('hello world', 'Hello', 'not_starts')); // case sensitive
        $this->assertFalse(Compare::values('hello', '', 'not_starts')); // empty string
    }

    /**
     * Test ends comparison
     */
    public function testEndsComparison()
    {
        $this->assertTrue(Compare::values('hello world', 'world', 'ends'));
        $this->assertTrue(Compare::values('hello world', 'd', 'ends'));
        $this->assertFalse(Compare::values('hello world', 'hello', 'ends'));
        $this->assertFalse(Compare::values('hello world', 'World', 'ends')); // case sensitive

        // Empty string
        $this->assertTrue(Compare::values('hello', '', 'ends'));

        // Full match
        $this->assertTrue(Compare::values('world', 'world', 'ends'));
    }

    /**
     * Test not_ends comparison
     */
    public function testNotEndsComparison()
    {
        $this->assertFalse(Compare::values('hello world', 'world', 'not_ends'));
        $this->assertTrue(Compare::values('hello world', 'hello', 'not_ends'));
        $this->assertTrue(Compare::values('hello world', 'World', 'not_ends')); // case sensitive
        $this->assertFalse(Compare::values('hello', '', 'not_ends')); // empty string
    }

    /**
     * Test regex comparison
     */
    public function testRegexComparison()
    {
        $this->assertTrue(Compare::values('hello123', '/\d+/', 'regex'));
        $this->assertTrue(Compare::values('Hello World', '/^Hello/', 'regex'));
        $this->assertTrue(Compare::values('test@example.com', '/^[\w\.-]+@[\w\.-]+\.\w+$/', 'regex'));
        $this->assertFalse(Compare::values('hello', '/\d+/', 'regex'));

        // Case insensitive regex
        $this->assertTrue(Compare::values('Hello', '/hello/i', 'regex'));
        $this->assertFalse(Compare::values('Hello', '/hello/', 'regex'));

        // Numeric values converted to string
        $this->assertTrue(Compare::values(12345, '/^\d+$/', 'regex'));
    }

    /**
     * Test not_regex comparison
     */
    public function testNotRegexComparison()
    {
        $this->assertFalse(Compare::values('hello123', '/\d+/', 'not_regex'));
        $this->assertTrue(Compare::values('hello', '/\d+/', 'not_regex'));
        $this->assertFalse(Compare::values('Hello World', '/^Hello/', 'not_regex'));
        $this->assertTrue(Compare::values('World Hello', '/^Hello/', 'not_regex'));
    }

    // FIXME after exceptions are enabled
//    public function testRegexWithInvalidPatternThrowsException()
//    {
//        $this->expectException(ErrorException::class);
//        Compare::values('test', '/[/', 'regex'); // unclosed bracket
//    }
//
//    public function testRegexWithInvalidDelimiterThrowsException()
//    {
//        $this->expectException(ErrorException::class);
//        Compare::values('test', 'invalid', 'regex'); // no delimiters
//    }
//
//    public function testNotRegexWithInvalidPatternThrowsException()
//    {
//        $this->expectException(ErrorException::class);
//        Compare::values('test', '/[/', 'not_regex');
//    }

    /**
     * Test in_array comparison
     */
    public function testInArrayComparison()
    {
        $array = [1, 2, 3, 'hello', 'world'];

        $this->assertTrue(Compare::values(1, $array, 'in_array'));
        $this->assertTrue(Compare::values('hello', $array, 'in_array'));
        $this->assertFalse(Compare::values(4, $array, 'in_array'));
        $this->assertFalse(Compare::values('Hello', $array, 'in_array')); // case sensitive

        // Type coercion (loose comparison in in_array)
        $this->assertTrue(Compare::values('1', $array, 'in_array'));
        $this->assertTrue(Compare::values(true, $array, 'in_array')); // true == 1

        // Empty array
        $this->assertFalse(Compare::values('anything', [], 'in_array'));
    }

    /**
     * Test not_in_array comparison
     */
    public function testNotInArrayComparison()
    {
        $array = [1, 2, 3, 'hello', 'world'];

        $this->assertFalse(Compare::values(1, $array, 'not_in_array'));
        $this->assertTrue(Compare::values(4, $array, 'not_in_array'));
        $this->assertTrue(Compare::values('Hello', $array, 'not_in_array')); // case sensitive
        $this->assertTrue(Compare::values('anything', [], 'not_in_array')); // empty array
    }

    /**
     * Test exists comparison
     */
    public function testExistsComparison()
    {
        // Test isset() behavior
        $value = 'test';
        $this->assertTrue(Compare::values($value, true, 'exists'));
        $this->assertFalse(Compare::values($value, false, 'exists'));

        $nullValue = null;
        $this->assertFalse(Compare::values($nullValue, true, 'exists'));
        $this->assertTrue(Compare::values($nullValue, false, 'exists'));

        // Unset variable would be null in this context
        $unsetVar = null;
        $this->assertFalse(Compare::values($unsetVar, true, 'exists'));
        $this->assertTrue(Compare::values($unsetVar, false, 'exists'));
    }

    /**
     * Test default case (invalid comparison operator)
     */
    public function testInvalidComparisonOperator()
    {
        $this->assertFalse(Compare::values(5, 5, 'invalid'));
        $this->assertFalse(Compare::values('hello', 'hello', 'unknown'));
        $this->assertFalse(Compare::values(true, true, ''));
    }

    /**
     * Test numeric casting behavior
     */
    public function testNumericCastingBehavior()
    {
        // Mock Number::cast behavior (assuming it converts strings to appropriate numeric types)
        // When one or both values are numeric, Number::cast should be called

        // Test with numeric strings
        $this->assertTrue(Compare::values('10', '5', '>'));
        $this->assertTrue(Compare::values('10.5', '10', '>'));
        $this->assertTrue(Compare::values(15, '10', '>'));

        // Test with mixed numeric/non-numeric (should still work due to PHP's type juggling)
        $this->assertTrue(Compare::values('10abc', '5', '>'));
    }

    /**
     * Test edge cases with null values
     */
    public function testNullValues()
    {
        $this->assertTrue(Compare::values(null, null, '='));
        $this->assertTrue(Compare::values(null, null, '=='));
        $this->assertFalse(Compare::values(null, 0, '=='));
        $this->assertTrue(Compare::values(null, 0, '='));
        $this->assertTrue(Compare::values(null, false, '='));
        $this->assertFalse(Compare::values(null, false, '=='));
    }

    /**
     * Test edge cases with boolean values
     */
    public function testBooleanValues()
    {
        $this->assertTrue(Compare::values(true, 1, '='));
        $this->assertFalse(Compare::values(true, 1, '=='));
        $this->assertTrue(Compare::values(false, 0, '='));
        $this->assertFalse(Compare::values(false, 0, '=='));
        $this->assertFalse(Compare::values(true, 'true', 'contains'));
        $this->assertFalse(Compare::values(false, 'false', 'contains'));
    }

    /**
     * Test edge cases with arrays
     */
    public function testArrayValues()
    {
        $array1 = [1, 2, 3, 'other'];

        // Array as second parameter for in_array
        $this->assertTrue(Compare::values(2, $array1, 'in_array'));
        $this->assertFalse(Compare::values(4, $array1, 'in_array'));
        $this->assertTrue(Compare::values('2', $array1, 'in_array')); // type coercion
        $this->assertFalse(Compare::values(2, $array1, 'not_in_array'));
        $this->assertTrue(Compare::values(4, $array1, 'not_in_array'));
        $this->assertTrue(Compare::values('not', $array1, 'not_in_array'));
        $this->assertTrue(Compare::values('other', $array1, 'in_array'));
    }

    /**
     * Test edge cases with floating point numbers
     */
    public function testFloatingPointNumbers()
    {
        $this->assertTrue(Compare::values(1.0, 1, '='));
        $this->assertFalse(Compare::values(1.0, 1, '=='));
        $this->assertFalse(Compare::values(0.1 + 0.2, 0.3, '=')); // Float precision issues

        // Very small differences
        $this->assertTrue(Compare::values(1.0000001, 1.0000002, '!='));
        $this->assertTrue(Compare::values(1.0000001, 1.0000002, '<'));
    }

    /**
     * Test with very large numbers
     */
    public function testLargeNumbers()
    {
        $large1 = PHP_INT_MAX;
        $large2 = PHP_INT_MAX - 1;

        $this->assertTrue(Compare::values($large1, $large2, '>'));
        $this->assertFalse(Compare::values($large1, $large2, '='));
        $this->assertTrue(Compare::values($large1, $large1, '='));
    }

    /**
     * Test with special string cases
     */
    public function testSpecialStringCases()
    {
        // Empty strings
        $this->assertTrue(Compare::values('', '', '='));
        $this->assertTrue(Compare::values('', '', '=='));
        $this->assertFalse(Compare::values('', ' ', '='));

        // Whitespace
        $this->assertFalse(Compare::values(' hello', 'hello', '='));
        $this->assertTrue(Compare::values(' hello', ' hello', '='));

        // Unicode characters
        $this->assertTrue(Compare::values('café', 'café', '='));
        $this->assertTrue(Compare::values('🌟', '🌟', '='));
        $this->assertTrue(Compare::values('test🌟', '🌟', 'contains'));

        // Very long strings
        $longString = str_repeat('a', 10000);
        $this->assertTrue(Compare::values($longString, $longString, '='));
        $this->assertTrue(Compare::values($longString, 'a', 'contains'));
        $this->assertTrue(Compare::values($longString, 'a', 'starts'));
        $this->assertTrue(Compare::values($longString, 'a', 'ends'));
    }

    /**
     * Test default comparison parameter
     */
    public function testDefaultComparisonParameter()
    {
        // Default should be '='
        $this->assertTrue(Compare::values(5, 5));
        $this->assertFalse(Compare::values(5, 6));
        $this->assertTrue(Compare::values('hello', 'hello'));
        $this->assertTrue(Compare::values('5', 5)); // loose equality
    }
}
