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
		if (this._group._spiderfied === this) {
			return;
		}

		var childMarkers = this.getAllChildMarkers(),
			group = this._group,
			map = group._map,
			center = map.latLngToLayerPoint(this._latlng),
			positions;

		this._group._unspiderfy();
		this._group._spiderfied = this;

		//TODO Maybe: childMarkers order by distance to center

		if (childMarkers.length >= this._circleSpiralSwitchover) {
			positions = this._generatePointsSpiral(childMarkers.length, center);
		} else {
			center.y += 10; //Otherwise circles look wrong
			positions = this._generatePointsCircle(childMarkers.length, center);
		}

		this._animationSpiderfy(childMarkers, positions);
	},

	unspiderfy: function () {

		this._animationUnspiderfy();

		this._group._spiderfied = null;
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

L.MarkerCluster.include(!L.DomUtil.TRANSITION ? {
	//Non Animated versions of everything
	_animationSpiderfy: function (childMarkers, positions) {
		var group = this._group,
			map = group._map,
			i, m, leg;

		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			m._backupPosSpider = m._latlng;
			m.setLatLng(map.layerPointToLatLng(positions[i]));
			m.setZIndexOffset(1000000); //Make these appear on top of EVERYTHING

			L.FeatureGroup.prototype.addLayer.call(group, m);

			leg = new L.Polyline([this._latlng, m._latlng], { weight: 1.5, color: '#222' });
			map.addLayer(leg);
			m._spiderLeg = leg;
		}
		this.setOpacity(0.3);
	},

	_animationUnspiderfy: function () {
		var group = this._group,
			map = group._map,
			childMarkers = this.getAllChildMarkers(),
			m, i;

		this.setOpacity(1);
		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			m.setLatLng(m._backupPosSpider);
			delete m._backupPosSpider;
			m.setZIndexOffset(0);

			L.FeatureGroup.prototype.removeLayer.call(group, m);

			map.removeLayer(m._spiderLeg);
			delete m._spiderLeg;
		}
	}
} : {
	//Animated versions here
	_animationSpiderfy: function (childMarkers, positions) {
		var me = this,
			group = this._group,
			map = group._map,
			i, m, leg;

		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			m._backupPosSpider = m._latlng;
			m.setLatLng(this._latlng);
			m.setZIndexOffset(1000000); //Make these appear on top of EVERYTHING
			m.setOpacity(0);

			L.FeatureGroup.prototype.addLayer.call(group, m);
		}

		setTimeout(function () {
			group._animationStart();

			var initialLegOpacity = L.Browser.svg ? 0 : 0.3,
				xmlns = L.Path.SVG_NS;


			for (i = childMarkers.length - 1; i >= 0; i--) {
				m = childMarkers[i];

				m.setLatLng(map.layerPointToLatLng(positions[i]));
				m.setOpacity(1);
				//Add Legs. TODO: Fade this in!

				leg = new L.Polyline([me._latlng, m._latlng], { weight: 1.5, color: '#222', opacity: initialLegOpacity });
				map.addLayer(leg);
				m._spiderLeg = leg;

				//Following animations don't work for canvas
				if (!L.Browser.svg) {
					continue;
				}

				//How this works:
				//http://stackoverflow.com/questions/5924238/how-do-you-animate-an-svg-path-in-ios
				//http://dev.opera.com/articles/view/advanced-svg-animation-techniques/

				//Animate length
				var length = leg._path.getTotalLength();
				leg._path.setAttribute("stroke-dasharray", length + "," + length);

				var anim = document.createElementNS(xmlns, "animate");
				anim.setAttribute("attributeName", "stroke-dashoffset");
				anim.setAttribute("begin", "indefinite");
				anim.setAttribute("from", length);
				anim.setAttribute("to", 0);
				anim.setAttribute("dur", 0.25);
				leg._path.appendChild(anim);
				anim.beginElement();

				//Animate opacity
				anim = document.createElementNS(xmlns, "animate");
				anim.setAttribute("attributeName", "stroke-opacity");
				anim.setAttribute("attributeName", "stroke-opacity");
				anim.setAttribute("begin", "indefinite");
				anim.setAttribute("from", 0);
				anim.setAttribute("to", 0.5);
				anim.setAttribute("dur", 0.25);
				leg._path.appendChild(anim);
				anim.beginElement();
			}
			me.setOpacity(0.3);

			//Set the opacity of the spiderLegs back to their correct value
			// The animations above override this until they complete.
			// Doing this at 250ms causes some minor flickering on FF, so just do it immediately
			// If the initial opacity of the spiderlegs isn't 0 then they appear before the animation starts.
			if (L.Browser.svg) {
				setTimeout(function () {
					for (i = childMarkers.length - 1; i >= 0; i--) {
						m = childMarkers[i]._spiderLeg;

						m.options.opacity = 0.5;
						m._path.setAttribute('stroke-opacity', 0.5);
					}
				}, 0);
			}

			setTimeout(function () {
				group._animationEnd();
			}, 250);
		}, 0);
	},

	_animationUnspiderfy: function () {
		var group = this._group,
			map = group._map,
			childMarkers = this.getAllChildMarkers(),
			svg = L.Browser.svg,
			m, i, a;

		group._animationStart();
		
		//Make us visible and bring the child markers back in
		this.setOpacity(1);
		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			m.setLatLng(this._latlng);
			m.setOpacity(0);

			//Animate the spider legs back in
			if (svg) {
				a = m._spiderLeg._path.childNodes[0];
				a.setAttribute('to', a.getAttribute('from'));
				a.setAttribute('from', 0);
				a.beginElement();

				a = m._spiderLeg._path.childNodes[1];
				a.setAttribute('from', 0.5);
				a.setAttribute('to', 0);
				a.setAttribute('stroke-opacity', 0);
				a.beginElement();

				m._spiderLeg._path.setAttribute('stroke-opacity', 0);
			}
		}

		setTimeout(function () {
			for (i = childMarkers.length - 1; i >= 0; i--) {
				m = childMarkers[i];
				m.setLatLng(m._backupPosSpider);
				delete m._backupPosSpider;
				m.setZIndexOffset(0);

				L.FeatureGroup.prototype.removeLayer.call(group, m);

				map.removeLayer(m._spiderLeg);
				delete m._spiderLeg;
			}
		}, 250);
	}
});


L.MarkerClusterGroup.include({
	//The MarkerCluster currently spiderfied (if any)
	_spiderfied: null,

	_spiderfierOnAdd: function () {
		this._map.on('click zoomstart', this._unspiderfy, this);

		if (L.Browser.svg) {
			this._map._initPathRoot(); //Needs to happen in the pageload, not after, or animations don't work in chrome
			//  http://stackoverflow.com/questions/8455200/svg-animate-with-dynamically-added-elements

		}
	},

	_unspiderfy: function () {
		if (this._spiderfied) {
			this._spiderfied.unspiderfy();
		}
	}
});