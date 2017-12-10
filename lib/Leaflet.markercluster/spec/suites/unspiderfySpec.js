describe('unspiderfy', function () {

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

	map = L.map(div, {
		maxZoom: 18
	});

	// Corresponds to zoom level 8 for the above div dimensions.
	map.fitBounds(new L.LatLngBounds([
		[1, 1],
		[2, 2]
	]));


	/////////////////////////////
	// TESTS
	/////////////////////////////

	it('Unspiderfies 2 Markers', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Marker([1.5, 1.5]);
		var marker2 = new L.Marker([1.5, 1.5]);

		group.addLayer(marker);
		group.addLayer(marker2);
		map.addLayer(group);

		marker.__parent.spiderfy();

		clock.tick(1000);

		group.unspiderfy();

		clock.tick(1000);

		expect(map.hasLayer(marker)).to.be(false);
		expect(map.hasLayer(marker2)).to.be(false);
	});

	it('Unspiderfies 2 CircleMarkers', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.CircleMarker([1.5, 1.5]);
		var marker2 = new L.CircleMarker([1.5, 1.5]);

		group.addLayer(marker);
		group.addLayer(marker2);
		map.addLayer(group);

		marker.__parent.spiderfy();

		clock.tick(1000);

		group.unspiderfy();

		clock.tick(1000);

		expect(map.hasLayer(marker)).to.be(false);
		expect(map.hasLayer(marker2)).to.be(false);
	});

	it('Unspiderfies 2 Circles', function () {

		group = new L.MarkerClusterGroup();

		var marker = new L.Circle([1.5, 1.5], 10);
		var marker2 = new L.Circle([1.5, 1.5], 10);

		group.addLayer(marker);
		group.addLayer(marker2);
		map.addLayer(group);

		marker.__parent.spiderfy();

		clock.tick(1000);

		group.unspiderfy();

		clock.tick(1000);

		expect(map.hasLayer(marker)).to.be(false);
		expect(map.hasLayer(marker2)).to.be(false);
	});

	it('fires unspiderfied event on unspiderfy', function (done) {

		group = new L.MarkerClusterGroup();

		var marker = new L.Marker([1.5, 1.5]);
		var marker2 = new L.Marker([1.5, 1.5]);

		group.addLayers([marker, marker2]);
		map.addLayer(group);

		marker.__parent.spiderfy();

		clock.tick(1000);

		// Add event listener
		group.on('unspiderfied', function (event) {
			expect(event.target).to.be(group);
			expect(event.cluster).to.be.a(L.Marker);
			expect(event.markers[1]).to.be(marker);
			expect(event.markers[0]).to.be(marker2);

			done();
		});

		group.unspiderfy();

		clock.tick(1000);

	});

});