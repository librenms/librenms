describe('addLayers adding multiple markers', function () {

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
			group.clearLayers();
			map.removeLayer(group);
		}

		// Throw away group as it can be assigned with different configurations between tests.
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

	it('creates a cluster when 2 overlapping markers are added before the group is added to the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Marker([1.5, 1.5]);
		var marker2 = new L.Marker([1.5, 1.5]);

		group.addLayers([marker, marker2]);
		map.addLayer(group);

		expect(marker._icon).to.be(undefined);
		expect(marker2._icon).to.be(undefined);

		expect(map._panes.markerPane.childNodes.length).to.be(1);
	});

	it('creates a cluster when 2 overlapping markers are added after the group is added to the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Marker([1.5, 1.5]);
		var marker2 = new L.Marker([1.5, 1.5]);

		map.addLayer(group);
		group.addLayers([marker, marker2]);

		expect(marker._icon).to.be(undefined);
		expect(marker2._icon).to.be(undefined);

		expect(map._panes.markerPane.childNodes.length).to.be(1);
	});

	it('creates a cluster and marker when 2 overlapping markers and one non-overlapping are added before the group is added to the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Marker([1.5, 1.5]);
		var marker2 = new L.Marker([1.5, 1.5]);
		var marker3 = new L.Marker([3.0, 1.5]);

		group.addLayers([marker, marker2, marker3]);
		map.addLayer(group);

		expect(marker._icon).to.be(undefined);
		expect(marker2._icon).to.be(undefined);
		expect(marker3._icon.parentNode).to.be(map._panes.markerPane);

		expect(map._panes.markerPane.childNodes.length).to.be(2);
	});

	it('creates a cluster and marker when 2 overlapping markers and one non-overlapping are added after the group is added to the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Marker([1.5, 1.5]);
		var marker2 = new L.Marker([1.5, 1.5]);
		var marker3 = new L.Marker([3.0, 1.5]);

		map.addLayer(group);
		group.addLayers([marker, marker2, marker3]);

		expect(marker._icon).to.be(undefined);
		expect(marker2._icon).to.be(undefined);
		expect(marker3._icon.parentNode).to.be(map._panes.markerPane);

		expect(map._panes.markerPane.childNodes.length).to.be(2);
	});

	it('handles nested Layer Groups', function () {

		group = new L.MarkerClusterGroup();

		var marker1 = new L.Marker([1.5, 1.5]);
		var marker2 = new L.Marker([1.5, 1.5]);
		var marker3 = new L.Marker([3.0, 1.5]);
		var layerGroup = new L.LayerGroup([marker1, new L.LayerGroup([marker2])]);

		map.addLayer(group);
		group.addLayers([layerGroup, marker3]);

		expect(marker1._icon).to.be(undefined);
		expect(marker2._icon).to.be(undefined);
		expect(marker3._icon.parentNode).to.be(map._panes.markerPane);

		expect(map._panes.markerPane.childNodes.length).to.be(2);
	});


	/////////////////////////////
	// CLEAN UP CODE
	/////////////////////////////

	map.remove();
	document.body.removeChild(div);

});
