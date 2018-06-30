describe('addLayer adding a single marker', function () {

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

	it('appears when added to the group before the group is added to the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Marker([1.5, 1.5]);

		group.addLayer(marker);
		map.addLayer(group);

		expect(marker._icon).to.not.be(undefined);
		expect(marker._icon.parentNode).to.be(map._panes.markerPane);
	});

	it('appears when added to the group after the group is added to the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Marker([1.5, 1.5]);

		map.addLayer(group);
		group.addLayer(marker);

		expect(marker._icon).to.not.be(undefined);
		expect(marker._icon.parentNode).to.be(map._panes.markerPane);
	});

	it('appears (using animations) when added after the group is added to the map', function () {

		group = new L.MarkerClusterGroup({ animateAddingMarkers: true });

		var marker = new L.Marker([1.5, 1.5]);

		map.addLayer(group);
		group.addLayer(marker);

		expect(marker._icon).to.not.be(undefined);
		expect(marker._icon.parentNode).to.be(map._panes.markerPane);
	});

	it('does not appear when too far away when added before the group is added to the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Marker([3.5, 1.5]);

		group.addLayer(marker);
		map.addLayer(group);

		expect(marker._icon).to.be(undefined);
	});

	it('does not appear when too far away when added after the group is added to the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Marker([3.5, 1.5]);

		map.addLayer(group);
		group.addLayer(marker);

		expect(marker._icon).to.be(undefined);
	});

	it('passes control to addLayers when marker is a Layer Group', function () {

		group = new L.MarkerClusterGroup();

		var marker1 = new L.Marker([1.5, 1.5]);
		var marker2 = new L.Marker([1.5, 1.5]);
		var layerGroup = new L.LayerGroup([marker1, marker2]);

		map.addLayer(group);
		group.addLayer(layerGroup);

		expect(group._topClusterLevel.getChildCount()).to.equal(2);

		expect(marker1._icon).to.be(undefined);
		expect(marker2._icon).to.be(undefined);

		expect(map._panes.markerPane.childNodes.length).to.be(1);
	});


	/////////////////////////////
	// CLEAN UP CODE
	/////////////////////////////

	map.remove();
	document.body.removeChild(div);

});
