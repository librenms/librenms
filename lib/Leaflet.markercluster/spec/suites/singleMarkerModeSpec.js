describe('singleMarkerMode option', function () {

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

		// Reset the marker icon.
		marker.setIcon(defaultIcon);

	});

	afterEach(function () {

		if (group instanceof L.MarkerClusterGroup) {
			group.removeLayers(group.getLayers());
			map.removeLayer(group);
		}

		// Throw away group as it can be assigned with different configurations between tests.
		group = null;

	});


	/////////////////////////////
	// PREPARATION CODE
	/////////////////////////////

	var div, map, group;

	var defaultIcon = new L.Icon.Default(),
	    clusterIcon = new L.Icon.Default(),
		marker = L.marker([1.5, 1.5]);

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

	it('overrides marker icons when set to true', function () {

		group = L.markerClusterGroup({
			singleMarkerMode: true,
			iconCreateFunction: function (layer) {
				return clusterIcon;
			}
		}).addTo(map);

		expect(marker.options.icon).to.equal(defaultIcon);

		marker.addTo(group);

		expect(marker.options.icon).to.equal(clusterIcon);

	});

	it('does not modify marker icons by default (or set to false)', function () {

		group = L.markerClusterGroup({
			iconCreateFunction: function (layer) {
				return clusterIcon;
			}
		}).addTo(map);

		expect(marker.options.icon).to.equal(defaultIcon);

		marker.addTo(group);

		expect(marker.options.icon).to.equal(defaultIcon);

	});


	/////////////////////////////
	// CLEAN UP CODE
	/////////////////////////////

	map.remove();
	document.body.removeChild(div);

});
