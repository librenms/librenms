describe('removeLayers', function () {

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

	it('removes all the layer given to it', function () {

		group = new L.MarkerClusterGroup();

		var markers = [
			new L.Marker([1.5, 1.5]),
			new L.Marker([1.5, 1.5]),
			new L.Marker([1.5, 1.5])
		];

		map.addLayer(group);

		group.addLayers(markers);

		group.removeLayers(markers);

		expect(group.hasLayer(markers[0])).to.be(false);
		expect(group.hasLayer(markers[1])).to.be(false);
		expect(group.hasLayer(markers[2])).to.be(false);

		expect(group.getLayers().length).to.be(0);
	});

	it('removes all the layer given to it even though they move', function () {

		group = new L.MarkerClusterGroup();

		var markers = [
			new L.Marker([10, 10]),
			new L.Marker([20, 20]),
			new L.Marker([30, 30])
		];
		var len = markers.length;
		map.addLayer(group);

		group.addLayers(markers);

		markers.forEach(function (marker) {
			marker.setLatLng([1.5, 1.5]);
			group.removeLayer(marker);
			expect(group.getLayers().length).to.be(len - 1);
			group.addLayer(marker);
			expect(group.getLayers().length).to.be(len);
		});

		expect(group.getLayers().length).to.be(len);
	});

	it('removes all the layer given to it even if the group is not on the map', function () {

		group = new L.MarkerClusterGroup();

		var markers = [
			new L.Marker([1.5, 1.5]),
			new L.Marker([1.5, 1.5]),
			new L.Marker([1.5, 1.5])
		];

		map.addLayer(group);
		group.addLayers(markers);
		map.removeLayer(group);
		group.removeLayers(markers);
		map.addLayer(group);

		expect(group.hasLayer(markers[0])).to.be(false);
		expect(group.hasLayer(markers[1])).to.be(false);
		expect(group.hasLayer(markers[2])).to.be(false);

		expect(group.getLayers().length).to.be(0);
	});

	it('doesnt break if we are spiderfied', function () {

		group = new L.MarkerClusterGroup();

		var markers = [
			new L.Marker([1.5, 1.5]),
			new L.Marker([1.5, 1.5]),
			new L.Marker([1.5, 1.5])
		];

		map.addLayer(group);

		group.addLayers(markers);

		markers[0].__parent.spiderfy();

		// We must wait for the spiderfy animation to timeout
		clock.tick(200);

		group.removeLayers(markers);

		expect(group.hasLayer(markers[0])).to.be(false);
		expect(group.hasLayer(markers[1])).to.be(false);
		expect(group.hasLayer(markers[2])).to.be(false);

		expect(group.getLayers().length).to.be(0);

		group.on('spiderfied', function() {
			expect(group._spiderfied).to.be(null);
		});
	});

	it('handles nested Layer Groups', function () {

		group = new L.MarkerClusterGroup();

		var marker1 = new L.Marker([1.5, 1.5]);
		var marker2 = new L.Marker([1.5, 1.5]);
		var marker3 = new L.Marker([1.5, 1.5]);

		map.addLayer(group);

		group.addLayers([marker1, marker2, marker3]);

		expect(group.hasLayer(marker1)).to.be(true);
		expect(group.hasLayer(marker2)).to.be(true);
		expect(group.hasLayer(marker3)).to.be(true);

		group.removeLayers([
			marker1,
			new L.LayerGroup([
				marker2, new L.LayerGroup([
					marker3
				])
			])
		]);

		expect(group.hasLayer(marker1)).to.be(false);
		expect(group.hasLayer(marker2)).to.be(false);
		expect(group.hasLayer(marker3)).to.be(false);

		expect(group.getLayers().length).to.be(0);
	});


	/////////////////////////////
	// CLEAN UP CODE
	/////////////////////////////

	map.remove();
	document.body.removeChild(div);

});
