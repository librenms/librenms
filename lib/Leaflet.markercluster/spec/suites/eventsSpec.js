describe('events', function() {
	var map, div;
	beforeEach(function() {
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
	afterEach(function() {
		document.body.removeChild(div);
	});

	it('is fired for a single child marker', function () {
		var callback = sinon.spy();
		var group = new L.MarkerClusterGroup();

		var marker = new L.Marker([1.5, 1.5]);

		group.on('click', callback);
		group.addLayer(marker);
		map.addLayer(group);

		marker.fire('click');

		expect(callback.called).to.be(true);
	});

	it('is fired for a child polygon', function () {
		var callback = sinon.spy();
		var group = new L.MarkerClusterGroup();

		var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

		group.on('click', callback);
		group.addLayer(polygon);
		map.addLayer(group);

		polygon.fire('click');

		expect(callback.called).to.be(true);
	});

	it('is fired for a cluster click', function () {
		var callback = sinon.spy();
		var group = new L.MarkerClusterGroup();

		var marker = new L.Marker([1.5, 1.5]);
		var marker2 = new L.Marker([1.5, 1.5]);

		group.on('clusterclick', callback);
		group.addLayers([marker, marker2]);
		map.addLayer(group);

		var cluster = group.getVisibleParent(marker);
		expect(cluster instanceof L.MarkerCluster).to.be(true);

		cluster.fire('click');

		expect(callback.called).to.be(true);
	});

	describe('after being added, removed, re-added from the map', function() {
		it('still fires events for nonpoint data', function() {
			var callback = sinon.spy();
			var group = new L.MarkerClusterGroup();

			var polygon = new L.Polygon([[1.5, 1.5], [2.0, 1.5], [2.0, 2.0], [1.5, 2.0]]);

			group.on('click', callback);
			group.addLayer(polygon);
			map.addLayer(group);
			map.removeLayer(group);
			map.addLayer(group);

			polygon.fire('click');

			expect(callback.called).to.be(true);
		});

		it('still fires events for point data', function() {
			var callback = sinon.spy();
			var group = new L.MarkerClusterGroup();

			var marker = new L.Marker([1.5, 1.5]);

			group.on('click', callback);
			group.addLayer(marker);
			map.addLayer(group);
			map.removeLayer(group);
			map.addLayer(group);

			marker.fire('click');

			expect(callback.called).to.be(true);
		});

		it('still fires cluster events', function() {
			var callback = sinon.spy();
			var group = new L.MarkerClusterGroup();

			var marker = new L.Marker([1.5, 1.5]);
			var marker2 = new L.Marker([1.5, 1.5]);

			group.on('clusterclick', callback);
			group.addLayers([marker, marker2]);
			map.addLayer(group);

			map.removeLayer(group);
			map.addLayer(group);

			var cluster = group.getVisibleParent(marker);
			expect(cluster instanceof L.MarkerCluster).to.be(true);

			cluster.fire('click');

			expect(callback.called).to.be(true);
		});

		it('doesnt break map events', function () {
			var callback = sinon.spy();
			var group = new L.MarkerClusterGroup();

			map.on('zoomend', callback);
			map.addLayer(group);

			map.removeLayer(group);
			map.addLayer(group);

			map.fire('zoomend');

			expect(callback.called).to.be(true);
		});
	});
	/*
	//No normal events can be fired by a clustered marker, so probably don't need this.
	it('is fired for a clustered child marker', function() {
		var callback = sinon.spy();
		var group = new L.MarkerClusterGroup();

		var marker = new L.Marker([1.5, 1.5]);
		var marker2 = new L.Marker([1.5, 1.5]);

		group.on('click', callback);
		group.addLayers([marker, marker2]);
		map.addLayer(group);

		marker.fire('click');

		expect(callback.called).to.be(true);
	});
	*/
});