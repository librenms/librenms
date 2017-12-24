describe('moving markers', function () {

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

		clock = sinon.useFakeTimers();

	});

	afterEach(function () {

		if (group instanceof L.MarkerClusterGroup) {
			group.clearLayers();
			map.removeLayer(group);
		}

		// Throw away group as it can be assigned with different configurations between tests.
		group = null;

		clock.restore();

	});


	/////////////////////////////
	// PREPARATION CODE
	/////////////////////////////

	var div, map, group, clock;

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

	it('moves a marker that was moved while off the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Marker([10, 10]);
		map.addLayer(group);
		group.addLayer(marker);

		map.removeLayer(group);
		marker.setLatLng([1.5, 1.5]);
		map.addLayer(group);

		expect(group.getLayers().length).to.be(1);
	});

	it('moves multiple markers that were moved while off the map', function () {

		group = new L.MarkerClusterGroup();
		map.addLayer(group);

		var markers = [];
		for (var i = 0; i < 10; i++) {
			var marker = new L.Marker([10, 10]);
			group.addLayer(marker);
			markers.push(marker);
		}

		map.removeLayer(group);
		for (var i = 0; i < 10; i++) {
			var marker = markers[i];
			marker.setLatLng([1.5, 1.5]);
		}
		map.addLayer(group);

		expect(group.getLayers().length).to.be(10);
	});


	/////////////////////////////
	// CLEAN UP CODE
	/////////////////////////////

	map.remove();
	document.body.removeChild(div);

});
