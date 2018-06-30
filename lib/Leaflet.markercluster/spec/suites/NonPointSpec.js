describe('adding non point data works', function () {

	/**
	 * Avoid as much as possible creating and destroying objects for each test.
	 * Instead, try re-using them, except for the ones under test of course.
	 * PhantomJS does not perform garbage collection for the life of the page,
	 * i.e. during the entire test process (Karma runs all tests in a single page).
	 * http://stackoverflow.com/questions/27239708/how-to-get-around-memory-error-with-karma-phantomjs
	 *
	 * The `beforeEach` and `afterEach do not seem to cause much issue.
	 * => they can still be used to initialize some setup between each test.
	 * Using them keeps a readable spec/index.
	 *
	 * But refrain from re-creating div and map every time. Re-use those objects.
	 */

	/////////////////////////////
	// SETUP FOR EACH TEST
	/////////////////////////////

	beforeEach(function () {

		// Nothing for this test suite.

	});

	afterEach(function () {

		if (group instanceof L.MarkerClusterGroup) {
			group.removeLayers(group.getLayers());
			map.removeLayer(group);
		}

		// group must be thrown away since we are testing it with a potentially
		// different configuration at each test.
		group = null;

	});


	/////////////////////////////
	// PREPARATION CODE
	/////////////////////////////

	var div, map, group;

	div = document.createElement('div');
	div.style.width = '200px';
	div.style.height = '200px';
	document.body.appendChild(div);

	map = L.map(div, { maxZoom: 18 });

	// Corresponds to zoom level 8 for the above div dimensions.
	map.fitBounds(new L.LatLngBounds([
		[1, 1],
		[2, 2]
	]));


	/////////////////////////////
	// TESTS
	/////////////////////////////

	it('Allows adding a polygon before via addLayer', function () {

		group = new L.MarkerClusterGroup();

		var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0,2.0], [1.5, 2.0]]);

		group.addLayer(polygon);
		map.addLayer(group);

		// Leaflet 1.0.0 now uses an intermediate L.Renderer.
		// polygon > _path > _rootGroup (g) > _container (svg) > pane (div)
		expect(polygon._path).to.not.be(undefined);
		expect(polygon._path.parentNode.parentNode.parentNode).to.be(map.getPane('overlayPane'));

		expect(group.hasLayer(polygon));
	});

	it('Allows adding a polygon before via addLayers([])', function () {

		group = new L.MarkerClusterGroup();

		var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

		group.addLayers([polygon]);
		map.addLayer(group);

		expect(polygon._path).to.not.be(undefined);
		expect(polygon._path.parentNode.parentNode.parentNode).to.be(map.getPane('overlayPane'));
	});

	it('Removes polygons from map when removed', function () {

		group = new L.MarkerClusterGroup();

		var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

		group.addLayer(polygon);
		map.addLayer(group);
		map.removeLayer(group);

		expect(polygon._path.parentNode).to.be(null);
	});

	describe('hasLayer', function () {

		it('returns false when not added', function () {
			group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			expect(group.hasLayer(polygon)).to.be(false);

			map.addLayer(group);

			expect(group.hasLayer(polygon)).to.be(false);

			map.addLayer(polygon);

			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('returns true before adding to map', function() {
			group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayers([polygon]);

			expect(group.hasLayer(polygon)).to.be(true);
		});

		it('returns true after adding to map after adding polygon', function () {
			group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayer(polygon);
			map.addLayer(group);

			expect(group.hasLayer(polygon)).to.be(true);
		});

		it('returns true after adding to map before adding polygon', function () {
			group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			map.addLayer(group);
			group.addLayer(polygon);

			expect(group.hasLayer(polygon)).to.be(true);
		});

	});

	describe('removeLayer', function() {

		it('removes before adding to map', function () {
			group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('removes before adding to map', function () {
			group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayers([polygon]);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('removes after adding to map after adding polygon', function () {
			group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayer(polygon);
			map.addLayer(group);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('removes after adding to map before adding polygon', function () {
			group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			map.addLayer(group);
			group.addLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(false);
		});

	});

	describe('removeLayers', function () {

		it('removes before adding to map', function () {
			group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayers([polygon]);
			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('removes before adding to map', function () {
			group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayers([polygon]);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayers([polygon]);
			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('removes after adding to map after adding polygon', function () {
			group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayer(polygon);
			map.addLayer(group);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayers([polygon]);
			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('removes after adding to map before adding polygon', function () {
			group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			map.addLayer(group);
			group.addLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayers([polygon]);
			expect(group.hasLayer(polygon)).to.be(false);
		});

	});


	/////////////////////////////
	// CLEAN UP CODE
	/////////////////////////////

	map.remove();
	document.body.removeChild(div);

});