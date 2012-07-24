(function () {
	L.MarkerClusterDefault = {
		iconCreateFunction: function (childCount) {
			var c = ' marker-cluster-';
			if (childCount < 10) {
				c += 'small';
			} else if (childCount < 100) {
				c += 'medium';
			} else {
				c += 'large';
			}

			return new L.DivIcon({ html: '<div><span>' + childCount + '</span></div>', className: 'marker-cluster' + c, iconSize: new L.Point(40, 40) });
		},

		_shownPolygon: null,

		bindEvents: function (map, markerClusterGroup) {
			var me = this;

			//Zoom on cluster click or spiderfy if we are at the lowest level
			markerClusterGroup.on('clusterclick', function (a) {
				if (map.getMaxZoom() === map.getZoom()) {
					a.layer.spiderfy();
				} else {
					a.layer.zoomToBounds();
				}
			});

			//Show convex hull (boundary) polygon on mouse over
			markerClusterGroup.on('clustermouseover', function (a) {
				if (me._shownPolygon) {
					map.removeLayer(me._shownPolygon);
				}
				if (a.layer.getChildCount() > 2) {
					me._shownPolygon = new L.Polygon(a.layer.getConvexHull());
					map.addLayer(me._shownPolygon);
				}
			});
			markerClusterGroup.on('clustermouseout', function (a) {
				if (me._shownPolygon) {
					map.removeLayer(me._shownPolygon);
					me._shownPolygon = null;
				}
			});
			map.on('zoomend', function () {
				if (me._shownPolygon) {
					map.removeLayer(me._shownPolygon);
					me._shownPolygon = null;
				}
			});
		}
	};
}());