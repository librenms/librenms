describe('adding non point data works', function () {
	var map, div;
	beforeEach(function () {
		div = document.createElement('div');
		div.style.width = '200px';
		div.style.height = '200px';
		document.body.appendChild(div);

		map = L.map(div, { maxZoom: 18 });

		map.fitBounds(new L.LatLngBounds([
			[1, 1],
			[2, 2]
		]));
	});
	afterEach(function () {
		document.body.removeChild(div);
	});

	it('Allows adding a polygon before via addLayer', function () {

		var group = new L.MarkerClusterGroup();
		var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0,2.0], [1.5, 2.0]]);

		group.addLayer(polygon);
		map.addLayer(group);

		expect(polygon._container).to.not.be(undefined);
		expect(polygon._container.parentNode).to.be(map._pathRoot);

		expect(group.hasLayer(polygon));
	});

	it('Allows adding a polygon before via addLayers([])', function () {

		var group = new L.MarkerClusterGroup();
		var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

		group.addLayers([polygon]);
		map.addLayer(group);

		expect(polygon._container).to.not.be(undefined);
		expect(polygon._container.parentNode).to.be(map._pathRoot);
	});

	it('Removes polygons from map when removed', function () {

		var group = new L.MarkerClusterGroup();
		var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

		group.addLayer(polygon);
		map.addLayer(group);
		map.removeLayer(group);

		expect(polygon._container.parentNode).to.be(null);
	});

	describe('hasLayer', function () {
		it('returns false when not added', function () {
			var group = new L.MarkerClusterGroup();
			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			expect(group.hasLayer(polygon)).to.be(false);

			map.addLayer(group);

			expect(group.hasLayer(polygon)).to.be(false);

			map.addLayer(polygon);

			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('returns true before adding to map', function() {
			var group = new L.MarkerClusterGroup();
			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayers([polygon]);

			expect(group.hasLayer(polygon)).to.be(true);
		});

		it('returns true after adding to map after adding polygon', function () {
			var group = new L.MarkerClusterGroup();
			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayer(polygon);
			map.addLayer(group);

			expect(group.hasLayer(polygon)).to.be(true);
		});

		it('returns true after adding to map before adding polygon', function () {
			var group = new L.MarkerClusterGroup();
			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			map.addLayer(group);
			group.addLayer(polygon);

			expect(group.hasLayer(polygon)).to.be(true);
		});
	});

	describe('removeLayer', function() {
		it('removes before adding to map', function () {
			var group = new L.MarkerClusterGroup();
			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('removes before adding to map', function () {
			var group = new L.MarkerClusterGroup();
			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayers([polygon]);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('removes after adding to map after adding polygon', function () {
			var group = new L.MarkerClusterGroup();
			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayer(polygon);
			map.addLayer(group);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('removes after adding to map before adding polygon', function () {
			var group = new L.MarkerClusterGroup();
			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			map.addLayer(group);
			group.addLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(false);
		});
	});

	describe('removeLayers', function () {
		it('removes before adding to map', function () {
			var group = new L.MarkerClusterGroup();
			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayers([polygon]);
			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('removes before adding to map', function () {
			var group = new L.MarkerClusterGroup();
			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayers([polygon]);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayers([polygon]);
			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('removes after adding to map after adding polygon', function () {
			var group = new L.MarkerClusterGroup();
			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.addLayer(polygon);
			map.addLayer(group);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayers([polygon]);
			expect(group.hasLayer(polygon)).to.be(false);
		});

		it('removes after adding to map before adding polygon', function () {
			var group = new L.MarkerClusterGroup();
			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			map.addLayer(group);
			group.addLayer(polygon);
			expect(group.hasLayer(polygon)).to.be(true);

			group.removeLayers([polygon]);
			expect(group.hasLayer(polygon)).to.be(false);
		});
	});
});