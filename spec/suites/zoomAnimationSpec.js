describe('eachLayer', function () {
	var map, div, clock;
	beforeEach(function () {
		clock = sinon.useFakeTimers();

		div = document.createElement('div');
		div.style.width = '200px';
		div.style.height = '200px';
		document.body.appendChild(div);

		map = L.map(div, { maxZoom: 18, center: new L.LatLng(-37.36142550190516, 174.254150390625), zoom: 7 });
	});
	afterEach(function () {
		clock.restore();

		document.body.removeChild(div);
	});

	it('adds the marker to the map', function () {
		var markers = new L.MarkerClusterGroup();
		var marker = new L.Marker([-37.77852090603777, 175.3103667497635]);
		markers.addLayer(marker); //The one we zoom in on
		markers.addLayer(new L.Marker([-37.711800591811055, 174.50034790039062])); //Marker that we cluster with at the top zoom level, but not 1 level down
		map.addLayer(markers);

		clock.tick(1000);
		map.setView([-37.77852090603777, 175.3103667497635], 15);
		//clock.tick(1000);
		//map.setView([-37.77852090603777, 175.3103667497635], 14);
		//clock.tick(1000);
		//map.setView([-37.77852090603777, 175.3103667497635], 15);

		//Run the the animation
		clock.tick(1000);

		expect(marker._icon).to.not.be(null);
	});
});