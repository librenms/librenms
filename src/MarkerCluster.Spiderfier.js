//This code is 100% based on https://github.com/jawj/OverlappingMarkerSpiderfier-Leaflet
//Huge thanks to jawj for implementing it first to make my job easy :-)

L.MarkerCluster.include({

	_2PI: Math.PI * 2,
	_circleFootSeparation: 25, //related to circumference of circle
	_circleStartAngle: Math.PI / 6,

	_spiralFootSeparation:  28, //related to size of spiral (experiment!)
	_spiralLengthStart: 11,
	_spiralLengthFactor: 5,

	_circleSpiralSwitchover: 9, //show spiral instead of circle from this marker count upwards.
								// 0 -> always spiral; Infinity -> always circle

	spiderfy: function () {
		var childMarkers = this.getAllChildMarkers(),
			group = this._group,
			map = group._map,
			center = map.latLngToLayerPoint(this._latlng),
			markerOffsets,
			i, m;
		//TODO Maybe: childMarkers order by distance to center

		if (childMarkers.length >= this._circleSpiralSwitchover) {
			markerOffsets = this._generatePointsSpiral(childMarkers.length, center);
		} else {
			center.y += 10; //Otherwise circles look wrong
			markerOffsets = this._generatePointsCircle(childMarkers.length, center);
		}

		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			m._backupPosSpider = m._latlng;
			m.setLatLng(map.layerPointToLatLng(markerOffsets[i]));

			m.setZIndexOffset(1000000); //Make these appear on top of EVERYTHING
			L.FeatureGroup.prototype.addLayer.call(group, m);

			var leg = new L.Polyline([this._latlng, m._latlng], { weight: 1.5, color: '#222' });
			map.addLayer(leg);
		}

		this.setOpacity(0.3);
	},

	unspiderfy: function () {
		if (false) {
			return;
		}
		//TODO
	},

	_generatePointsCircle: function (count, centerPt) {
		var circumference = this._circleFootSeparation * (2 + count),
			legLength = circumference / this._2PI,  //radius from circumference
			angleStep = this._2PI / count,
			res = [],
			i, angle;

		res.length = count;

		for (i = count - 1; i >= 0; i--) {
			angle = this._circleStartAngle + i * angleStep;
			res[i] = new L.Point(centerPt.x + legLength * Math.cos(angle), centerPt.y + legLength * Math.sin(angle));
		}

		return res;
	},

	_generatePointsSpiral: function (count, centerPt) {
		var legLength = this._spiralLengthStart,
			angle = 0,
			res = [],
			i;

		res.length = count;

		for (i = count - 1; i >= 0; i--) {
			angle += this._spiralFootSeparation / legLength + i * 0.0005;
			res[i] = new L.Point(centerPt.x + legLength * Math.cos(angle), centerPt.y + legLength * Math.sin(angle));
			legLength += this._2PI * this._spiralLengthFactor / angle;
		}
		return res;
	}
});