describe('onAdd', function () {
	var map, div;
	beforeEach(function () {
		div = document.createElement('div');
		div.style.width = '200px';
		div.style.height = '200px';
		document.body.appendChild(div);

		map = L.map(div);

		map.fitBounds(new L.LatLngBounds([
			[1, 1],
			[2, 2]
		]));
	});
	afterEach(function () {
		document.body.removeChild(div);
	});


	it('throws an error if maxZoom is not specified', function () {

		var group = new L.MarkerClusterGroup();
		var marker = new L.Marker([1.5, 1.5]);

		group.addLayer(marker);

		var ex = null;
		try {
			map.addLayer(group);
		} catch (e) {
			ex = e;
		}

		expect(ex).to.not.be(null);
	});
});