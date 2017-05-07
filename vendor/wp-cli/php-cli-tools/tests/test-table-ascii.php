<?php

use cli\Streams;
use cli\Table;
use cli\table\Ascii;
use cli\Colors;

/**
 * Class Test_Table_Ascii
 *
 * Acceptance tests for ASCII table drawing.
 * It will redirect STDOUT to temporary file and check that output matches with expected
 */
class Test_Table_Ascii extends PHPUnit_Framework_TestCase {
	/**
	 * @var string Path to temporary file, where STDOUT output will be redirected during tests
	 */
	private $_mockFile;
	/**
	 * @var \cli\Table Instance
	 */
	private $_instance;

	/**
	 * Creates instance and redirects STDOUT to temporary file
	 */
	public function setUp() {
		$this->_mockFile = tempnam(sys_get_temp_dir(), 'temp');
		$resource = fopen($this->_mockFile, 'wb');
		Streams::setStream('out', $resource);

		$this->_instance = new Table();
		$this->_instance->setRenderer(new Ascii());
	}

	/**
	 * Cleans temporary file
	 */
	public function tearDown() {
		if (file_exists($this->_mockFile)) {
			unlink($this->_mockFile);
		}
	}

	/**
	 * Draw simple One column table
	 */
	public function testDrawOneColumnTable() {
		$headers = array('Test Header');
		$rows = array(
			array('x'),
		);
		$output = <<<'OUT'
+-------------+
| Test Header |
+-------------+
| x           |
+-------------+

OUT;
		$this->assertInOutEquals(array($headers, $rows), $output);
	}

	/**
	 * Draw simple One column table with colored string
	 * Output should look like:
	 * +-------------+
	 * | Test Header |
	 * +-------------+
	 * | x           |
	 * +-------------+
	 *
	 * where `x` character has green color.
	 * At the same time it checks that `green` defined in `cli\Colors` really looks as `green`.
	 */
	public function testDrawOneColumnColoredTable() {
		Colors::enable( true );
		$headers = array('Test Header');
		$rows = array(
			array(Colors::colorize('%Gx%n', true)),
		);
		// green `x`
		$x = "\x1B\x5B\x33\x32\x3B\x31\x6Dx\x1B\x5B\x30\x6D";
		$output = <<<OUT
+-------------+
| Test Header |
+-------------+
| $x           |
+-------------+

OUT;
		$this->assertInOutEquals(array($headers, $rows), $output);
	}

	/**
	 * Checks that spacing and borders are handled correctly in table
	 */
	public function testSpacingInTable() {
		$headers = array('A', '    ', 'C', '');
		$rows = array(
			array('     ', 'B1', '', 'D1'),
			array('A2', '', ' C2', null),
		);
		$output = <<<'OUT'
+-------+------+-----+----+
| A     |      | C   |    |
+-------+------+-----+----+
|       | B1   |     | D1 |
| A2    |      |  C2 |    |
+-------+------+-----+----+

OUT;
		$this->assertInOutEquals(array($headers, $rows), $output);
	}

	/**
	 * Test correct table indentation and border positions for multibyte strings
	 */
	public function testTableWithMultibyteStrings() {
		$headers = array('German', 'French', 'Russian', 'Chinese');
		$rows = array(
			array('Schätzen', 'Apprécier', 'Оценить', '欣賞'),
		);
		$output = <<<'OUT'
+----------+-----------+---------+---------+
| German   | French    | Russian | Chinese |
+----------+-----------+---------+---------+
| Schätzen | Apprécier | Оценить | 欣賞    |
+----------+-----------+---------+---------+

OUT;
		$this->assertInOutEquals(array($headers, $rows), $output);
	}

	/**
	 * Test that % gets escaped correctly.
	 */
	public function testTableWithPercentCharacters() {
		$headers = array( 'Heading', 'Heading2', 'Heading3' );
		$rows = array(
			array( '% at start', 'at end %', 'in % middle' )
		);
		$output = <<<'OUT'
+------------+----------+-------------+
| Heading    | Heading2 | Heading3    |
+------------+----------+-------------+
| % at start | at end % | in % middle |
+------------+----------+-------------+

OUT;
		$this->assertInOutEquals(array($headers, $rows), $output);
	}

	/**
	 * Test that a % is appropriately padded in the table
	 */
	public function testTablePaddingWithPercentCharacters() {
		$headers = array( 'ID', 'post_title', 'post_name' );
		$rows = array(
			array(
				3,
				'10%',
				''
			),
			array(
				1,
				'Hello world!',
				'hello-world'
			),
		);
		$output = <<<'OUT'
+----+--------------+-------------+
| ID | post_title   | post_name   |
+----+--------------+-------------+
| 3  | 10%          |             |
| 1  | Hello world! | hello-world |
+----+--------------+-------------+

OUT;
		$this->assertInOutEquals(array($headers, $rows), $output);
	}

	/**
	 * Draw wide multiplication Table.
	 * Example with many columns, many rows
	 */
	public function testDrawMultiplicationTable() {
		$maxFactor = 16;
		$headers = array_merge(array('x'), range(1, $maxFactor));
		for ($i = 1, $rows = array(); $i <= $maxFactor; ++$i) {
			$rows[] = array_merge(array($i), range($i, $i * $maxFactor, $i));
		}

		$output = <<<'OUT'
+----+----+----+----+----+----+----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+
| x  | 1  | 2  | 3  | 4  | 5  | 6  | 7   | 8   | 9   | 10  | 11  | 12  | 13  | 14  | 15  | 16  |
+----+----+----+----+----+----+----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+
| 1  | 1  | 2  | 3  | 4  | 5  | 6  | 7   | 8   | 9   | 10  | 11  | 12  | 13  | 14  | 15  | 16  |
| 2  | 2  | 4  | 6  | 8  | 10 | 12 | 14  | 16  | 18  | 20  | 22  | 24  | 26  | 28  | 30  | 32  |
| 3  | 3  | 6  | 9  | 12 | 15 | 18 | 21  | 24  | 27  | 30  | 33  | 36  | 39  | 42  | 45  | 48  |
| 4  | 4  | 8  | 12 | 16 | 20 | 24 | 28  | 32  | 36  | 40  | 44  | 48  | 52  | 56  | 60  | 64  |
| 5  | 5  | 10 | 15 | 20 | 25 | 30 | 35  | 40  | 45  | 50  | 55  | 60  | 65  | 70  | 75  | 80  |
| 6  | 6  | 12 | 18 | 24 | 30 | 36 | 42  | 48  | 54  | 60  | 66  | 72  | 78  | 84  | 90  | 96  |
| 7  | 7  | 14 | 21 | 28 | 35 | 42 | 49  | 56  | 63  | 70  | 77  | 84  | 91  | 98  | 105 | 112 |
| 8  | 8  | 16 | 24 | 32 | 40 | 48 | 56  | 64  | 72  | 80  | 88  | 96  | 104 | 112 | 120 | 128 |
| 9  | 9  | 18 | 27 | 36 | 45 | 54 | 63  | 72  | 81  | 90  | 99  | 108 | 117 | 126 | 135 | 144 |
| 10 | 10 | 20 | 30 | 40 | 50 | 60 | 70  | 80  | 90  | 100 | 110 | 120 | 130 | 140 | 150 | 160 |
| 11 | 11 | 22 | 33 | 44 | 55 | 66 | 77  | 88  | 99  | 110 | 121 | 132 | 143 | 154 | 165 | 176 |
| 12 | 12 | 24 | 36 | 48 | 60 | 72 | 84  | 96  | 108 | 120 | 132 | 144 | 156 | 168 | 180 | 192 |
| 13 | 13 | 26 | 39 | 52 | 65 | 78 | 91  | 104 | 117 | 130 | 143 | 156 | 169 | 182 | 195 | 208 |
| 14 | 14 | 28 | 42 | 56 | 70 | 84 | 98  | 112 | 126 | 140 | 154 | 168 | 182 | 196 | 210 | 224 |
| 15 | 15 | 30 | 45 | 60 | 75 | 90 | 105 | 120 | 135 | 150 | 165 | 180 | 195 | 210 | 225 | 240 |
| 16 | 16 | 32 | 48 | 64 | 80 | 96 | 112 | 128 | 144 | 160 | 176 | 192 | 208 | 224 | 240 | 256 |
+----+----+----+----+----+----+----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+

OUT;
		$this->assertInOutEquals(array($headers, $rows), $output);
	}

	/**
	 * Draw a table with headers but no data
	 */
	public function testDrawWithHeadersNoData() {
		$headers = array('header 1', 'header 2');
		$rows = array();
		$output = <<<'OUT'
+----------+----------+
| header 1 | header 2 |
+----------+----------+
+----------+----------+

OUT;
		$this->assertInOutEquals(array($headers, $rows), $output);
	}

	/**
	 * Verifies that Input and Output equals,
	 * Sugar method for fast access from tests
	 *
	 * @param array $input First element is header array, second element is rows array
	 * @param mixed $output Expected output
	 */
	private function assertInOutEquals(array $input, $output) {
		$this->_instance->setHeaders($input[0]);
		$this->_instance->setRows($input[1]);
		$this->_instance->display();
		$this->assertOutFileEqualsWith($output);
	}

	/**
	 * Checks that contents of input string and temporary file match
	 *
	 * @param mixed $expected Expected output
	 */
	private function assertOutFileEqualsWith($expected) {
		$this->assertEquals($expected, file_get_contents($this->_mockFile));
	}
}
