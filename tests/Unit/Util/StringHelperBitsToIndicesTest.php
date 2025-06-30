<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Util\StringHelpers;
use PHPUnit\Framework\TestCase;

include_once 'includes/functions.php';

class StringHelperBitsToIndicesTest extends TestCase
{
    /**
     * Test the basic functionality with the example from the comment
     */
    public function testBasicFunctionality(): void
    {
        $result = StringHelpers::bitsToIndices('9a00');
        $this->assertEquals([1, 4, 5, 7], $result);
    }

    /**
     * Test with single hex digit (should be padded with leading zero)
     */
    public function testSingleHexDigit(): void
    {
        // 'f' -> '0f' -> '00001111' -> [5, 6, 7, 8]
        $result = StringHelpers::bitsToIndices('f');
        $this->assertEquals([5, 6, 7, 8], $result);
    }

    /**
     * Test with odd number of hex digits
     */
    public function testOddNumberOfDigits(): void
    {
        // 'abc' -> '0abc' -> binary analysis
        $result = StringHelpers::bitsToIndices('abc');
        // '0abc' = 0000 1010 1011 1100
        // Byte 1: 0000 1010 -> positions 5, 7
        // Byte 2: 1011 1100 -> positions 9, 11, 12, 13, 14
        $this->assertEquals([5, 7, 9, 11, 12, 13, 14], $result);
    }

    /**
     * Test with all zeros
     */
    public function testAllZeros(): void
    {
        $result = StringHelpers::bitsToIndices('0000');
        $this->assertEquals([], $result);
    }

    /**
     * Test with all ones
     */
    public function testAllOnes(): void
    {
        $result = StringHelpers::bitsToIndices('ff');
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8], $result);
    }

    /**
     * Test with mixed case hex
     */
    public function testMixedCaseHex(): void
    {
        $result = StringHelpers::bitsToIndices('AbC');
        // Same as 'abc' test
        $this->assertEquals([5, 7, 9, 11, 12, 13, 14], $result);
    }

    /**
     * Test with spaces in hex string
     */
    public function testWithSpaces(): void
    {
        $result = StringHelpers::bitsToIndices('9a 00');
        $this->assertEquals([1, 4, 5, 7], $result);
    }

    /**
     * Test with newlines in hex string
     */
    public function testWithNewlines(): void
    {
        $result = StringHelpers::bitsToIndices("9a\n00");
        $this->assertEquals([1, 4, 5, 7], $result);
    }

    /**
     * Test with mixed whitespace
     */
    public function testWithMixedWhitespace(): void
    {
        $result = StringHelpers::bitsToIndices("9a \n 00");
        $this->assertEquals([1, 4, 5, 7], $result);
    }

    /**
     * Test with empty string
     */
    public function testEmptyString(): void
    {
        $result = StringHelpers::bitsToIndices('');
        $this->assertEquals([], $result);
    }

    /**
     * Test with invalid hex characters
     */
    public function testInvalidHexCharacters(): void
    {
        $result = StringHelpers::bitsToIndices('xyz');
        $this->assertEquals([], $result);
    }

    /**
     * Test with mixed valid and invalid hex characters
     */
    public function testMixedValidInvalidHex(): void
    {
        $result = StringHelpers::bitsToIndices('a1z3');
        $this->assertEquals([], $result);
    }

    /**
     * Test with longer hex string
     */
    public function testLongerHexString(): void
    {
        // 'ff00ff' -> 11111111 00000000 11111111
        // Positions: 1-8, 17-24
        $result = StringHelpers::bitsToIndices('ff00ff');
        $expected = array_merge(
            range(1, 8),    // First byte: ff
            range(17, 24)   // Third byte: ff
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * Test bit position calculation for specific patterns
     */
    public function testBitPositionCalculation(): void
    {
        // '80' -> 10000000 -> [1] (leftmost bit)
        $result = StringHelpers::bitsToIndices('80');
        $this->assertEquals([1], $result);

        // '01' -> 00000001 -> [8] (rightmost bit)
        $result = StringHelpers::bitsToIndices('01');
        $this->assertEquals([8], $result);

        // '8001' -> 10000000 00000001 -> [1, 16]
        $result = StringHelpers::bitsToIndices('8001');
        $this->assertEquals([1, 16], $result);
    }

    /**
     * Test with only whitespace
     */
    public function testOnlyWhitespace(): void
    {
        $result = StringHelpers::bitsToIndices("  \n  ");
        $this->assertEquals([], $result);
    }

    /**
     * Test edge case with single bit in each nibble
     */
    public function testSingleBitPerNibble(): void
    {
        // '88' -> 10001000 -> [1, 5]
        $result = StringHelpers::bitsToIndices('88');
        $this->assertEquals([1, 5], $result);
    }

    /**
     * Test alternating bit pattern
     */
    public function testAlternatingBits(): void
    {
        // 'aa' -> 10101010 -> [1, 3, 5, 7]
        $result = StringHelpers::bitsToIndices('aa');
        $this->assertEquals([1, 3, 5, 7], $result);

        // '55' -> 01010101 -> [2, 4, 6, 8]
        $result = StringHelpers::bitsToIndices('55');
        $this->assertEquals([2, 4, 6, 8], $result);
    }

    /**
     * Test with very long hex string to ensure indexing works correctly
     */
    public function testVeryLongHexString(): void
    {
        // Test with 4 bytes: first and last byte set to 0x80
        $result = StringHelpers::bitsToIndices('80008080');
        // Byte 1: 10000000 -> [1]
        // Byte 2: 00000000 -> []
        // Byte 3: 10000000 -> [17]
        // Byte 4: 10000000 -> [25]
        $this->assertEquals([1, 17, 25], $result);
    }

    /**
     * Data provider for testing various hex values and their expected indices
     */
    public static function hexDataProvider(): array
    {
        return [
            'single_bit_first_position' => ['80', [1]],
            'single_bit_last_position' => ['01', [8]],
            'two_bytes_alternating' => ['aa55', [1, 3, 5, 7, 10, 12, 14, 16]],
            'three_bytes_pattern' => ['f0f0f0', [1, 2, 3, 4, 9, 10, 11, 12, 17, 18, 19, 20]],
            'zero_byte_in_middle' => ['ff00ff', array_merge(range(1, 8), range(17, 24))],
        ];
    }

    /**
     * @dataProvider hexDataProvider
     */
    public function testWithDataProvider(string $hex, array $expected): void
    {
        $result = StringHelpers::bitsToIndices($hex);
        $this->assertEquals($expected, $result);
    }
}
