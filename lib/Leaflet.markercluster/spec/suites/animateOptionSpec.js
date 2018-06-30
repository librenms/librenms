describe('animate option', function () {

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

		previousTransitionSetting = L.DomUtil.TRANSITION;

	});

	afterEach(function () {

		// Restore previous setting so that next tests are unaffected.
		L.DomUtil.TRANSITION = previousTransitionSetting;

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

	var div, map, group, previousTransitionSetting;

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

	it('hooks animated methods version by default', function () {

		// Need to add to map so that we have the top cluster level created.
		group = L.markerClusterGroup().addTo(map);

		var withAnimation = L.MarkerClusterGroup.prototype._withAnimation;

		// MCG animated methods.
		expect(group._animationStart).to.be(withAnimation._animationStart);
		expect(group._animationZoomIn).to.be(withAnimation._animationZoomIn);
		expect(group._animationZoomOut).to.be(withAnimation._animationZoomOut);
		expect(group._animationAddLayer).to.be(withAnimation._animationAddLayer);

		// MarkerCluster spiderfy animated methods
		var cluster = group._topClusterLevel;

		withAnimation = L.MarkerCluster.prototype;

		expect(cluster._animationSpiderfy).to.be(withAnimation._animationSpiderfy);
		expect(cluster._animationUnspiderfy).to.be(withAnimation._animationUnspiderfy);

	});

	it('hooks non-animated methods version when set to false', function () {

		// Need to add to map so that we have the top cluster level created.
		group = L.markerClusterGroup({animate: false}).addTo(map);

		var noAnimation = L.MarkerClusterGroup.prototype._noAnimation;

		// MCG non-animated methods.
		expect(group._animationStart).to.be(noAnimation._animationStart);
		expect(group._animationZoomIn).to.be(noAnimation._animationZoomIn);
		expect(group._animationZoomOut).to.be(noAnimation._animationZoomOut);
		expect(group._animationAddLayer).to.be(noAnimation._animationAddLayer);

		// MarkerCluster spiderfy non-animated methods
		var cluster = group._topClusterLevel;

		noAnimation = L.MarkerClusterNonAnimated.prototype;

		expect(cluster._animationSpiderfy).to.be(noAnimation._animationSpiderfy);
		expect(cluster._animationUnspiderfy).to.be(noAnimation._animationUnspiderfy);

	});

	it('always hooks non-animated methods version when L.DomUtil.TRANSITION is false', function () {

		// Fool Leaflet, make it think the browser does not support transitions.
		L.DomUtil.TRANSITION = false;

		// Need to add to map so that we have the top cluster level created.
		group = L.markerClusterGroup({animate: true}).addTo(map);

		var noAnimation = L.MarkerClusterGroup.prototype._noAnimation;

		// MCG non-animated methods.
		expect(group._animationStart).to.be(noAnimation._animationStart);
		expect(group._animationZoomIn).to.be(noAnimation._animationZoomIn);
		expect(group._animationZoomOut).to.be(noAnimation._animationZoomOut);
		expect(group._animationAddLayer).to.be(noAnimation._animationAddLayer);

		// MarkerCluster spiderfy non-animated methods
		var cluster = group._topClusterLevel;

		noAnimation = L.MarkerClusterNonAnimated.prototype;

		expect(cluster._animationSpiderfy).to.be(noAnimation._animationSpiderfy);
		expect(cluster._animationUnspiderfy).to.be(noAnimation._animationUnspiderfy);

	});


	/////////////////////////////
	// CLEAN UP CODE
	/////////////////////////////

	map.remove();
	document.body.removeChild(div);

});
