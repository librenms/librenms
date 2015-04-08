/*global QUnit:false, module:false, test:false, asyncTest:false, expect:false*/
/*global start:false, stop:false ok:false, equal:false, notEqual:false, deepEqual:false*/
/*global notDeepEqual:false, strictEqual:false, notStrictEqual:false, raises:false*/
(function($) {

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
  setup: function() {

   this.el = $('#qunit-fixture').find(".wrapper ul");

   this.serialization = [
    { name: "A", col: "1",  row: "1",  size_x: "2",  size_y: "2"  },
    { name: "B", col: "4",  row: "1",  size_x: "1",  size_y: "2"  },
    { name: "C", col: "10", row: "10", size_x: "10", size_y: "10" },
    { name: "D", col: "3",  row: "1",  size_x: "1",  size_y: "1"  },
    { name: "E", col: "2",  row: "3",  size_x: "3",  size_y: "1"  }
   ];
  }
 });

  test('is chainable', 1, function() {
    // Not a bad test to run on collection methods.
    strictEqual(this.el, this.el.gridster(), 'should be chaninable');
  });

 test('Gridster.sort_by_row_asc', function(assert) {
  var sorted = Gridster.sort_by_row_asc(this.serialization);

  var result = pickup(sorted, 'name').join(',');
  var expected = 'A,B,D,E,C';
  assert.equal(result, expected);
 });

 test('Gridster.sort_by_row_and_col_asc', function(assert) {
  var sorted = Gridster.sort_by_row_and_col_asc(this.serialization);

  var result = pickup(sorted, 'name').join(',');
  var expected = 'A,D,B,E,C';
  assert.equal(result, expected);
 });

 test('Gridster.sort_by_col_asc', function(assert) {
  var sorted = Gridster.sort_by_col_asc(this.serialization);

  var result = pickup(sorted, 'name').join(',');
  var expected = 'A,E,D,B,C';
  assert.equal(result, expected);
 });

 test('Gridster.sort_by_row_desc', function(assert) {
  var sorted = Gridster.sort_by_row_desc(this.serialization);

  var result = pickup(sorted, 'name').join(',');
  var expected = 'C,E,A,B,D';
  assert.equal(result, expected);
 });

 // erros
 test('Throws not exists property', function(assert) {
  assert.throws(function() {
    var data = [{row:1, size_x:1, size_y:1},{col:2,row:1,size_x:1,size_y:1}];
    Gridster.sort_by_row_asc(data);
   },
   Error,
   'raise error not exists required property'
  );
 });

 test('Throws invalid type of value', function(assert) {
  // inconvertible types
  assert.throws(function() {
    Gridster.sort_by_row_asc([{col:"AAA", row:1, size_x:1, size_y:1},{col:2,row:1,size_x:1,size_y:1}]);
   },
   Error,
   'raise error inconvertible types'
  );

  // null
  assert.throws(function() {
    Gridster.sort_by_row_asc([{col:null, row:1, size_x:1, size_y:1},{col:2,row:1,size_x:1,size_y:1}]);
   },
   Error,
   'raise error value is null'
  );

  // array
  assert.throws(function() {
    Gridster.sort_by_row_asc([{col:[1,2,3], row:1, size_x:1, size_y:1},{col:2,row:1,size_x:1,size_y:1}]);
   },
   Error,
   'raise error value is array'
  );

  // object
  assert.throws(function() {
    Gridster.sort_by_row_asc([{col:{k:1}, row:1, size_x:1, size_y:1},{col:2,row:1,size_x:1,size_y:1}]);
   },
   Error,
   'raise error value is object'
  );
 });

 // helper
 function pickup(data, prop) {
  return data.map(function(elm) {
   return elm[prop];
  });
 }
}(jQuery));