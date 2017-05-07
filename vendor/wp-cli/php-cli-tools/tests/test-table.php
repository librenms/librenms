<?php

/**
 * Tests for cli\Table
 */
class Test_Table extends PHPUnit_Framework_TestCase {

	public function test_column_value_too_long() {

		$constraint_width = 80;

		$table = new cli\Table;
		$renderer = new cli\Table\Ascii;
		$renderer->setConstraintWidth( $constraint_width );
		$table->setRenderer( $renderer );
		$table->setHeaders( array( 'Field', 'Value' ) );
		$table->addRow( array( 'description', 'The 2012 theme for WordPress is a fully responsive theme that looks great on any device. Features include a front page template with its own widgets, an optional display font, styling for post formats on both index and single views, and an optional no-sidebar page template. Make it yours with a custom menu, header image, and background.' ) );
		$table->addRow( array( 'author', '<a href="http://wordpress.org/" title="Visit author homepage">the WordPress team</a>' ) );

		$out = $table->getDisplayLines();
		// "+ 1" accommodates "\n"
		$this->assertCount( 12, $out );
		$this->assertEquals( $constraint_width, strlen( $out[0] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[1] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[2] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[3] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[4] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[5] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[6] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[7] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[8] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[9] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[10] ) + 1 );
		$this->assertEquals( $constraint_width, strlen( $out[11] ) + 1 );

	}

}