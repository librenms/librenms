describe('distance grid', function () {
	it('addObject', function () {
		var grid = new L.DistanceGrid(100),
		    obj = {};

		expect(grid.addObject(obj, { x: 0, y: 0 })).to.eql(undefined);
		expect(grid.removeObject(obj, { x: 0, y: 0 })).to.eql(true);
	});

	it('eachObject', function (done) {
		var grid = new L.DistanceGrid(100),
		    obj = {};

		expect(grid.addObject(obj, { x: 0, y: 0 })).to.eql(undefined);

		grid.eachObject(function(o) {
			expect(o).to.eql(obj);
			done();
		});
	});
});
