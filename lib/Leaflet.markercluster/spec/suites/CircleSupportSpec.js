describe('support for Circle elements', function () {

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
			group.removeLayers(group.getLayers());
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

	it('appears when added to the group before the group is added to the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Circle([1.5, 1.5], 200);

		group.addLayer(marker);
		map.addLayer(group);

		// Leaflet 1.0.0 now uses an intermediate L.Renderer.
		// marker > _path > _rootGroup (g) > _container (svg) > pane (div)
		expect(marker._path.parentNode).to.not.be(undefined);
		expect(marker._path.parentNode.parentNode.parentNode).to.be(map.getPane('overlayPane'));

		clock.tick(1000);
	});

	it('appears when added to the group after the group is added to the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Circle([1.5, 1.5], 200);

		group.addLayer(marker);
		map.addLayer(group);

		expect(marker._path.parentNode).to.not.be(undefined);
		expect(marker._path.parentNode.parentNode.parentNode).to.be(map.getPane('overlayPane'));

		clock.tick(1000);
	});

	it('appears animated when added to the group after the group is added to the map', function () {

		group = new L.MarkerClusterGroup({ animateAddingMarkers: true });

		var marker = new L.Circle([1.5, 1.5], 200);
		var marker2 = new L.Circle([1.5, 1.5], 200);

		map.addLayer(group);
		group.addLayer(marker);
		group.addLayer(marker2);

		expect(marker._path.parentNode.parentNode.parentNode).to.be(map.getPane('overlayPane'));
		expect(marker2._path.parentNode.parentNode.parentNode).to.be(map.getPane('overlayPane'));

		clock.tick(1000);
	});

	it('creates a cluster when 2 overlapping markers are added before the group is added to the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Circle([1.5, 1.5], 200);
		var marker2 = new L.Circle([1.5, 1.5], 200);

		group.addLayers([marker, marker2]);
		map.addLayer(group);

		expect(marker._path).to.be(undefined);
		expect(marker2._path).to.be(undefined);

		expect(map._panes.markerPane.childNodes.length).to.be(1);

		clock.tick(1000);
	});

	it('creates a cluster when 2 overlapping markers are added after the group is added to the map', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Circle([1.5, 1.5], 200);
		var marker2 = new L.Circle([1.5, 1.5], 200);

		map.addLayer(group);
		group.addLayer(marker);
		group.addLayer(marker2);

		expect(marker._path.parentNode).to.be(null); //Removed then re-added, so null
		expect(marker2._path).to.be(undefined);

		expect(map._panes.markerPane.childNodes.length).to.be(1);

		clock.tick(1000);
	});

	it('disappears when removed from the group', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Circle([1.5, 1.5], 200);

		group.addLayer(marker);
		map.addLayer(group);

		expect(marker._path.parentNode).to.not.be(undefined);
		expect(marker._path.parentNode.parentNode.parentNode).to.be(map.getPane('overlayPane'));

		group.removeLayer(marker);

		expect(marker._path.parentNode).to.be(null);

		clock.tick(1000);
	});


	/////////////////////////////
	// CLEAN UP CODE
	/////////////////////////////

	map.remove();
	document.body.removeChild(div);

});