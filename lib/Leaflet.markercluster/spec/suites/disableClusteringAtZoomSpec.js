describe('disableClusteringAtZoom option', function () {

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

		// group must be thrown away since we are testing it with a potentially
		// different configuration at each test.
		group = null;

		clock.restore();
		clock = null;

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

	it('unclusters at zoom level equal or higher', function () {

		var maxZoom = 15;

		group = new L.MarkerClusterGroup({
			disableClusteringAtZoom: maxZoom
		});

		group.addLayers([
			new L.Marker([1.5, 1.5]),
			new L.Marker([1.5, 1.5])
		]);
		map.addLayer(group);

		expect(group._maxZoom).to.equal(maxZoom - 1);

		expect(map._panes.markerPane.childNodes.length).to.equal(1); // 1 cluster.

		map.setZoom(14);
		clock.tick(1000);
		expect(map._panes.markerPane.childNodes.length).to.equal(1); // 1 cluster.

		map.setZoom(15);
		clock.tick(1000);
		expect(map._panes.markerPane.childNodes.length).to.equal(2); // 2 markers.
	});


	/////////////////////////////
	// CLEAN UP CODE
	/////////////////////////////

	map.remove();
	document.body.removeChild(div);

});
