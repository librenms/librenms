//jshint quotmark:false
/*global module:false, test:false */
/*global ok:false, equal:false, notEqual:false, deepEqual:false*/
/*global notDeepEqual:false */
(function ($) {
'use strict';
	/*
	 ======== A Handy Little QUnit Reference ========
	 http://docs.jquery.com/QUnit

	 Test methods:
	 expect(numAssertions)
	 stop(increment)
	 start(decrement)
	 Test assertions:
	 ok(value, [message])
	 equal(actual, expected, [message])
	 notEqual(actual, expected, [message])
	 deepEqual(actual, expected, [message])
	 notDeepEqual(actual, expected, [message])
	 strictEqual(actual, expected, [message])
	 notStrictEqual(actual, expected, [message])
	 raises(block, [expected], [message])
	 */

	module('jQuery#gridster', {
		setup: function () {

			this.el = $('#qunit-fixture').find(".wrapper ul");

			this.serialization = [
				{name: "A", col: "1", row: "1", size_x: "2", size_y: "2"},
				{name: "B", col: "4", row: "1", size_x: "1", size_y: "2"},
				{name: "C", col: "10", row: "10", size_x: "10", size_y: "10"},
				{name: "D", col: "3", row: "1", size_x: "1", size_y: "1"},
				{name: "E", col: "2", row: "3", size_x: "3", size_y: "1"}
			];

			this.serialization_small = [
				{col: 1, row: 1, size_x: 2, size_y: 2},
				{col: 3, row: 1, size_x: 1, size_y: 2},
				{col: 6, row: 1, size_x: 1, size_y: 1}
			];
		}
	});

	test('can count and clear widgets', 2, function () {
		var grid = this.el.gridster().data('gridster');
		equal(grid.get_num_widgets(), 2, 'Count the default widgets from the HTML config');
		grid.remove_all_widgets();
		equal(grid.get_num_widgets(), 0, 'Clearing the widgets to prepare for tests');
	});

	test('Above and below', 12, function () {
		var grid = this.el.gridster({ max_cols: 4, max_rows: 4, widget_base_dimensions: [100, 55]}).data('gridster');
		//remove any widgets from the html config
		grid.remove_all_widgets();
		equal(grid.get_num_widgets(), 0, 'Clearing the widgets to prepare for tests');
		$.each(this.serialization, function () {
			grid.add_widget('<li />', this.size_x, this.size_y, this.col, this.row);
		});
		equal(grid.get_num_widgets(), this.serialization.length, 'Loaded the widgets for the test');
		var widgets_above = $([]);
		//the call here checks above as the user see's it on the screen which will check row below 4
		grid.for_each_widget_above(3, 4, function(tcol, trow) {
			widgets_above = widgets_above.add(this);
		});
		//widgets B (3,1) & E (2-4, 3) should be below cell 3,4
		equal(2, widgets_above.length);
		var widgets_found_above = grid.serialize(widgets_above);
		equal(widgets_found_above[1].col, parseInt(this.serialization[4].col));
		equal(widgets_found_above[1].row, parseInt(this.serialization[4].row));
		equal(widgets_found_above[1].size_x, parseInt(this.serialization[4].size_x));
		equal(widgets_found_above[1].size_y, parseInt(this.serialization[4].size_y));


		var widgets_below = $([]);
		grid.for_each_widget_below(3, 2, function(tcol, trow) {
			widgets_below = widgets_below.add(this);
		});
		//widget E (2-4, 3) should be above cell 3,2
		equal(1, widgets_below.length);
		var widgets_found_below = grid.serialize(widgets_below);
		equal(widgets_found_below[0].col, parseInt(this.serialization[4].col));
		equal(widgets_found_below[0].row, parseInt(this.serialization[4].row));
		equal(widgets_found_below[0].size_x, parseInt(this.serialization[4].size_x));
		equal(widgets_found_below[0].size_y, parseInt(this.serialization[4].size_y));
	});

	test('get_widgets_from', 5, function () {
		var input = {col: 2, row: 1, size_x: 3, size_y: 1};
		var grid = this.el.gridster().data('gridster');
		//remove any widgets from the html config
		grid.remove_all_widgets();
		equal(grid.get_num_widgets(), 0, 'Clearing the widgets to prepare for tests');
		grid.add_widget('<li />', input.size_x, input.size_y, input.col, input.row);


		var widget = grid.get_widgets_at_cell(input.col, input.row);
		// normally you would call parseInt on a return from
		// .attr(), but qunit takes care of it for us
		equal(widget.attr('data-row'), input.row);
		equal(widget.attr('data-col'), input.col);
		equal(widget.attr('data-sizex'), input.size_x);
		equal(widget.attr('data-sizey'), input.size_y);
	});

	test('get_cells_occupied', 3, function () {
		var input = {col: 2, row: 3, size_x: 3, size_y: 1};
		var grid = this.el.gridster().data('gridster');
		//remove any widgets from the html config
		grid.remove_all_widgets();
		equal(grid.get_num_widgets(), 0, 'Clearing the widgets to prepare for tests');

		var cellsUsed = grid.get_cells_occupied(input);
		deepEqual(cellsUsed.rows, [3]);
		deepEqual(cellsUsed.cols, [2,3,4]);
	});

	test('get_highest_occupied_cell', 1, function () {
		var grid = this.el.gridster().data('gridster');
		deepEqual(grid.get_min_col(), 1);
	});

	test('get_highest_occupied_cell', 1, function () {
		var grid = this.el.gridster().data('gridster');
		deepEqual(grid.get_highest_occupied_cell(), {col: 3, row: 2});
	});

	//todo tests to add:
	// setup_resize & add_resize_handle
	// get_min_col
	// shift_cols
	// get_widgets_from_DOM dom_to_coords, get_widgets_from_DOM set_dom_grid_height set_dom_grid_width
	// generate_stylesheet
	// set_num_columns


	test('add_style_tag', 4, function () {
		var grid = this.el.gridster({autogenerate_stylesheet: true}).data('gridster');
		var generatedStyleSheet = $('#gridster-stylesheet');
		notEqual(generatedStyleSheet, null);
		ok(generatedStyleSheet.length > 0);

		grid.destroy();
		grid = this.el.gridster({autogenerate_stylesheet: true, namespace: 'qunit'}).data('gridster');
		generatedStyleSheet = $('#gridster-stylesheet-qunit');
		notEqual(generatedStyleSheet, null);
		ok(generatedStyleSheet.length > 0);
	});

	test('resize_widget', 4, function () {
		this.resizeGrid = [
			{col: 1, row: 1, size_x: 1, size_y: 1},
			{col: 2, row: 1, size_x: 1, size_y: 1},
			{col: 3, row: 1, size_x: 1, size_y: 1},
			{col: 1, row: 2, size_x: 1, size_y: 1},
			{col: 2, row: 2, size_x: 1, size_y: 1},
			{col: 3, row: 2, size_x: 1, size_y: 1},
			{col: 1, row: 3, size_x: 1, size_y: 1},
			{col: 2, row: 3, size_x: 1, size_y: 1},
			{col: 3, row: 3, size_x: 1, size_y: 1}
		];

		var grid = this.el.gridster({widget_base_dimensions: [100, 55]}).data('gridster');
		//remove any widgets from the html config
		grid.remove_all_widgets();
		var numBefore = grid.get_num_widgets();
		equal(grid.get_num_widgets(), 0, 'Clearing the widgets to prepare for tests');
		$.each(this.resizeGrid, function () {
			grid.add_widget('<li />', this.size_x, this.size_y, this.col, this.row);
		});
		equal(grid.get_num_widgets(), numBefore + this.resizeGrid.length, 'Loading the widgets to prepare for tests');

		//check for widgets in the space it will occupy
		var widgets = grid.get_widgets_in_range(1,1,2,2);
		var numberInSpaceBefore = widgets.length;
		equal(numberInSpaceBefore, 4, 'Expect there to be four widgets in the first two rows and cols');

		//get the widget from 1,1 and resize it.
		grid.resize_widget(grid.get_widgets_at_cell(1, 1), 2, 2);

		//check for widgets in the space it will occupy
		widgets = grid.get_widgets_in_range(1,1,2,2);
		var numberInSpaceAfter = widgets.length;
		equal(numberInSpaceAfter, 1, 'Expected a single widget in the expanded area');

	});

	test('can serialize correctly', 4, function () {
		var grid = this.el.gridster({widget_base_dimensions: [100, 55]}).data('gridster');
		//remove any widgets from the html config
		grid.remove_all_widgets();
		var numBefore = grid.get_num_widgets();
		equal(grid.get_num_widgets(), 0, 'Clearing the widgets to prepare for tests');
		$.each(this.serialization_small, function () {
			grid.add_widget('<li />', this.size_x, this.size_y, this.col, this.row);
		});
		equal(grid.get_num_widgets(), numBefore + this.serialization_small.length);
		var serialized = grid.serialize();
		equal(grid.get_num_widgets(), serialized.length);
		deepEqual(serialized, this.serialization_small);
	});

	test('can serialize extended properties', 4, function () {
		var input = [{col: 6, row: 3, size_x: 1, size_y: 1}];
		var grid = this.el.gridster({widget_base_dimensions: [100, 55], serialize_params: function($w, wgd) {
			return {
				col: wgd.col,
				row: wgd.row
			};}}).data('gridster');
		//remove any widgets from the html config
		grid.remove_all_widgets();
		equal(grid.get_num_widgets(), 0, 'Clearing the widgets to prepare for tests');
		grid.add_widget('<li />', input[0].size_x, input[0].size_y, input[0].col, input[0].row);
		var serialized = grid.serialize();
		//due to custom serialization, input and output should NOT match
		notDeepEqual(serialized, input);
		equal(serialized[0].col, 6);
		equal(serialized[0].size_x, undefined);
	});

	test('When Adding widgets rows auto condense', 2, function () {
		var input = [{col: 6, row: 3, size_x: 1, size_y: 1}];
		var output = [{col: 6, row: 1, size_x: 1, size_y: 1}];
		var grid = this.el.gridster().data('gridster');
		//remove any widgets from the html config
		grid.remove_all_widgets();
		//make sure we are empty
		equal(grid.get_num_widgets(), 0, 'Clearing the widgets to prepare for tests');
		grid.add_widget('<li />', input[0].size_x, input[0].size_y, input[0].col, input[0].row);
		var serialized = grid.serialize();
		deepEqual(serialized, output);
	});

	test('When Adding widgets rows static placement is supported', 2, function () {
		var input = [{col: 6, row: 3, size_x: 1, size_y: 1}];
		var grid = this.el.gridster().data('gridster');
		grid.options.shift_widgets_up = false;
		//remove any widgets from the html config
		grid.remove_all_widgets();
		//make sure we are empty
		equal(grid.get_num_widgets(), 0, 'Clearing the widgets to prepare for tests');
		grid.add_widget('<li />', input[0].size_x, input[0].size_y, input[0].col, input[0].row);
		var serialized = grid.serialize();
		deepEqual(serialized, input);
	});

	test('When Adding widgets cols are respected', 2, function () {
		var input = [{col: 6, row: 1, size_x: 1, size_y: 1}];
		var grid = this.el.gridster().data('gridster');
		//remove any widgets from the html config
		grid.remove_all_widgets();
		//make sure we are empty
		equal(grid.get_num_widgets(), 0, 'Clearing the widgets to prepare for tests');
		grid.add_widget('<li />', input[0].size_x, input[0].size_y, input[0].col, input[0].row);
		var serialized = grid.serialize();
		deepEqual(serialized, input);
	});

	test('can_move_to', 7, function () {
		var input = {col: 6, row: 1, size_x: 1, size_y: 1};
		var defaultGrid = this.el.gridster().data('gridster');
		//remove any widgets from the html config
		defaultGrid.remove_all_widgets();
		//make sure we are empty
		equal(defaultGrid.get_num_widgets(), 0, 'Clearing the widgets to prepare for tests');
		//check with the default config we can place an widget in a skipped col
		var canMove = defaultGrid.can_move_to({size_x: input.size_x, size_y: input.size_y}, input.col, input.row);
		equal(canMove, true, 'with the default config we can place an widget in a skipped col');
		//check with the default config we can not place an widget in a skipped row
		canMove = defaultGrid.can_move_to({size_x: input.size_x, size_y: input.size_y}, input.col, input.row+3);
		equal(canMove, true, 'with the default config we can not place an widget in a skipped row');
		defaultGrid.destroy();

		//now repeat the tests with custom settings
		var customGrid = this.el.gridster({max_rows : 2, max_cols : 4}).data('gridster');
		//remove any widgets from the html config
		customGrid.remove_all_widgets();
		//make sure we are empty
		equal(customGrid.get_num_widgets(), 0, 'Clearing the widgets to prepare for tests');
		//check with the Custom config we can place an widget outside the grid
		canMove = customGrid.can_move_to({size_x: input.size_x, size_y: input.size_y}, input.col, input.row);
		equal(canMove, false, 'with the Custom config we can place an widget outside the grid');
		//check with the custom config we can not place an widget outside the grid
		canMove = customGrid.can_move_to({size_x: input.size_x, size_y: input.size_y}, 1, input.row+3);
		equal(canMove, false, 'with the custom config we can not place an widget outside the grid');
		//check to see if we can't move an widget to where there is one
		customGrid.add_widget('<li />', 1, 1, 1, 1);
		canMove = customGrid.can_move_to({size_x: 1, size_y: 1}, 1, 1);
		equal(canMove, false, 'we cant move an widget to where there is one');

	});

	test('is chainable', 1, function () {
		// Not a bad test to run on collection methods.
		strictEqual(this.el, this.el.gridster(), 'should be chaninable');
	});

	test('is Responsive', 1, function () {
		var grid = this.el.gridster(
				{autogenerate_stylesheet: true,
				 widget_base_dimensions: ['auto', 'auto'],
				 max_cols: 4}).data('gridster');
		equal(grid.is_responsive(), true);
	});

	test('Gridster.sort_by_row_asc', 4, function (assert) {
		var sorted = Gridster.sort_by_row_asc(this.serialization);

		var result = pickup(sorted, 'name').join(',');
		var expected = 'A,B,D,E,C';
		//since the test data contains 3 #1, they could be in any order
		ok( result.substring(0,5).indexOf('A') > -1, 'A is found in within the first 3 results');
		ok( result.substring(0,5).indexOf('B') > -1, 'B is found in within the first 3 results');
		ok( result.substring(0,5).indexOf('D') > -1, 'D is found in within the first 3 results');
		//check the last to chars
		equal( result.substring(6), expected.substring(6), 'E,C are the last two - In that order');
	});

	test('Gridster.sort_by_row_and_col_asc', function (assert) {
		var sorted = Gridster.sort_by_row_and_col_asc(this.serialization);

		var result = pickup(sorted, 'name').join(',');
		var expected = 'A,D,B,E,C';
		assert.equal(result, expected);
	});

	test('Gridster.sort_by_col_asc', function (assert) {
		var sorted = Gridster.sort_by_col_asc(this.serialization);

		var result = pickup(sorted, 'name').join(',');
		var expected = 'A,E,D,B,C';
		assert.equal(result, expected);
	});

	test('Gridster.sort_by_row_desc',4,  function (assert) {
		var sorted = Gridster.sort_by_row_desc(this.serialization);

		var result = pickup(sorted, 'name').join(',');
		var expected = 'C,E,A,B,D';
		//since the test data contains 3 #1, they could be in any order
		ok( result.substring(4).indexOf('A') > -1, 'A is found in within the last 3 results');
		ok( result.substring(4).indexOf('B') > -1, 'B is found in within the last 3 results');
		ok( result.substring(4).indexOf('D') > -1, 'D is found in within the last 3 results');
		//check the last to chars
		equal( result.substring(0,3), expected.substring(0,3), 'C,E are the first two - In that order');
	});

	// errors
	test('sort_by_row_asc: Throws not exists property', function (assert) {
		assert.throws(function () {
					//missing col
					var data = [{row: 1, size_x: 1, size_y: 1}, {col: 2, row: 1, size_x: 1, size_y: 1}];
					Gridster.sort_by_row_asc(data);
				},
				Error,
				'raise error not exists required property'
		);
	});

	test('sort_by_row_asc: Throws invalid type of value', function (assert) {
		var secWidget = {col: 2, row: 1, size_x: 1, size_y: 1};
		// inconvertible types
		assert.throws(function () {
					//col is not a number
					Gridster.sort_by_row_asc([{col: "AAA", row: 1, size_x: 1, size_y: 1}, secWidget]);
				}, Error, 'raise error inconvertible types' );

		// null
		assert.throws(function () {
					// coll is null
					Gridster.sort_by_row_asc([{col: null, row: 1, size_x: 1, size_y: 1}, secWidget]);
				}, Error, 'raise error value is null' );

		// array
		assert.throws(function () {
					//col does not accept an array
					Gridster.sort_by_row_asc([{col: [1, 2, 3], row: 1, size_x: 1, size_y: 1}, secWidget]);
				}, Error, 'raise error value is array' );

		// object
		assert.throws(function () {
					//col does not accept an onbject
					Gridster.sort_by_row_asc([{col: {k: 1}, row: 1, size_x: 1, size_y: 1}, secWidget]);
				},Error, 'raise error value is object');
	});

	// helper
	function pickup (data, prop) {
		return data.map(function (elm) {
			return elm[prop];
		});
	}
}(jQuery));