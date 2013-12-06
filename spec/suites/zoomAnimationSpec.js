describe('zoomAnimation', function () {
	var map, div, clock;
	beforeEach(function () {
		clock = sinon.useFakeTimers();

		div = document.createElement('div');
		div.style.width = '200px';
		div.style.height = '200px';
		document.body.appendChild(div);

		map = L.map(div, { maxZoom: 18 });
	});
	afterEach(function () {
		clock.restore();

		document.body.removeChild(div);
	});

	it('adds the visible marker to the map when zooming in', function () {
		map.setView(new L.LatLng(-37.36142550190516, 174.254150390625), 7);

		var markers = new L.MarkerClusterGroup({
			showCoverageOnHover: true,
			maxClusterRadius: 20,
			disableClusteringAtZoom: 15
		});
		var marker = new L.Marker([-37.77852090603777, 175.3103667497635]);
		markers.addLayer(marker); //The one we zoom in on
		markers.addLayer(new L.Marker([-37.711800591811055, 174.50034790039062])); //Marker that we cluster with at the top zoom level, but not 1 level down
		map.addLayer(markers);

		clock.tick(1000);
		map.setView([-37.77852090603777, 175.3103667497635], 15);

		//Run the the animation
		clock.tick(1000);

		expect(marker._icon).to.not.be(undefined);
		expect(marker._icon).to.not.be(null);
	});

	it('adds the visible marker to the map when jumping around', function () {

		var markers = new L.MarkerClusterGroup();
		var marker1 = new L.Marker([48.858280181884766, 2.2945759296417236]);
		var marker2 = new L.Marker([16.02359962463379, -61.70280075073242]);
		markers.addLayer(marker1); //The one we zoom in on first
		markers.addLayer(marker2); //Marker that we cluster with at the top zoom level, but not 1 level down
		map.addLayer(markers);

		//show the first
		map.fitBounds(new L.LatLngBounds(new L.LatLng(41.371582, -5.142222), new L.LatLng(51.092804, 9.561556)));

		clock.tick(1000);

		map.fitBounds(new L.LatLngBounds(new L.LatLng(15.830972671508789, -61.807167053222656), new L.LatLng(16.516849517822266, -61.0)));

		//Run the the animation
		clock.tick(1000);

		//Now the second one should be visible on the map
		expect(marker2._icon).to.not.be(undefined);
		expect(marker2._icon).to.not.be(null);
	});

	it('adds the visible markers to the map, but not parent clusters when jumping around', function () {

		var markers = new L.MarkerClusterGroup(),
			marker1 = new L.Marker([59.9520, 30.3307]),
			marker2 = new L.Marker([59.9516, 30.3308]),
			marker3 = new L.Marker([59.9513, 30.3312]);

		markers.addLayer(marker1);
		markers.addLayer(marker2);
		markers.addLayer(marker3);
		map.addLayer(markers);

		//Show none of them
		map.setView([53.0676, 170.6835], 16);

		clock.tick(1000);

		//Zoom so that all the markers will be visible (Same as zoomToShowLayer)
		map.setView(marker1.getLatLng(), 18);

		//Run the the animation
		clock.tick(1000);

		//Now the markers should all be visible, and there should be no visible clusters
		expect(marker1._icon.parentNode).to.be(map._panes.markerPane);
		expect(marker2._icon.parentNode).to.be(map._panes.markerPane);
		expect(marker3._icon.parentNode).to.be(map._panes.markerPane);
		expect(map._panes.markerPane.childNodes.length).to.be(3);
	});
});