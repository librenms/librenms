
/*
 * L.MarkerClusterGroup extends L.FeatureGroup by clustering the markers contained within
 */

L.MarkerClusterGroup = L.FeatureGroup.extend({

	options: {
		//distanceToCluster: 10, //Any points closer than this will probably get put in to a cluster
		maxClusterRadius: 60, //A cluster will cover at most this many pixels from its center
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
		}
	},

	initialize: function (options) {
		L.Util.setOptions(this, options);

		L.FeatureGroup.prototype.initialize.call(this, []);

		this._inZoomAnimation = 0;
		this._needsClustering = [];

		this._markersAndClustersAtZoom = {};
	},


	_sqDist: function (p1, p2) {
		var dx = p2.x - p1.x,
			dy = p2.y - p1.y;
		return dx * dx + dy * dy;
	},

	_zoomEnd: function () {
		this._animationStart();

		this._mergeSplitClusters();

		this._zoom = this._map._zoom;
	},

	_moveEnd: function () {
		if (this._inZoomAnimation > 0) {
			return;
		}

		var l, i,
		    layers = this._layers,
		    bounds = this._getExpandedVisibleBounds(),
		    highestLevel = this._markersAndClustersAtZoom[this._highestZoom],
		    depth = this._zoom - this._highestZoom,
		    highestLevelClusters = highestLevel.clusters,
		    highestLevelUnclustered = highestLevel.unclustered;

		//Remove visible layers that are no longer visible
		for (i in layers) {
			l = layers[i];
			if (!bounds.contains(l.getLatLng())) {
				L.FeatureGroup.prototype.removeLayer.call(this, l);
			}
		}

		//Re-Check everyone for being in the viewport
		//Do the clusters (and their child unclustered ones) recursively for performance
		for (i = 0; i < highestLevelClusters.length; i++) {
			l = highestLevelClusters[i];
			if (bounds.intersects(l._bounds)) {
				l._recursivelyAddChildrenToMap(null, depth, bounds);
			}
		}

		//Do the markers at this level too
		for (i = 0; i < highestLevelUnclustered.length; i++) {
			l = highestLevelUnclustered[i];
			if (bounds.contains(l.getLatLng())) {
				L.FeatureGroup.prototype.addLayer.call(this, l);
			}
		}
	},

	_generateInitialClusters: function () {
		var res = this._cluster([], [], this._needsClustering, this._map.getZoom());

		this._markersAndClustersAtZoom[this._map._zoom] = res;
		//Remember the highest zoom level we've seen and the current zoom level
		this._highestZoom = this._zoom = this._map._zoom;

		//Make things appear on the map
		for (var i = 0; i < res.clusters.length; i++) {
			//TODO: Bounds
			res.clusters[i]._addToMap();
		}
		for (var j = 0; j < res.unclustered.length; j++) {
			//TODO: Bounds
			L.FeatureGroup.prototype.addLayer.call(this, res.unclustered[j]);
		}
	},

	//Merge and split any existing clusters that are too big or small
	_mergeSplitClusters: function () {
		var newState,
		    depth = Math.abs(this._map._zoom - this._zoom);

		if (this._zoom < this._map._zoom) { //Zoom in, split
			var startingClusters = this._markersAndClustersAtZoom[this._zoom].clusters,
			    startingUnclustered = this._markersAndClustersAtZoom[this._zoom].unclustered;

			while (this._zoom < this._map._zoom) { //Split each intermediate layer if required
				var currentClusters = this._markersAndClustersAtZoom[this._zoom].clusters;

				this._zoom++;

				newState = this._markersAndClustersAtZoom[this._zoom];

				if (!newState) { //If we don't have clusters for the new level, calculate them
					newState = { 'clusters': [], 'unclustered': [] };

					for (var i = 0; i < currentClusters.length; i++) {
						var newClusters;
						if (currentClusters[i]._childClusters.length > 0) {

							//Child clusters should always be 0 as we haven't calculated clusters for this level
							throw 'something is wrong, childClusters length should be 0: ' + currentClusters[i]._childClusters.length;
						} else {
							newClusters = this._cluster([], [], currentClusters[i]._markers, this._zoom);
						}

						currentClusters[i]._childClusters = newClusters.clusters;
						currentClusters[i]._markers = newClusters.unclustered;

						newState.clusters = newState.clusters.concat(newClusters.clusters);
						newState.unclustered = newState.unclustered.concat(newClusters.unclustered);
					}

					this._markersAndClustersAtZoom[this._zoom] = newState;
				}
			}

			this._animationZoomIn(startingClusters, startingUnclustered, depth);

		} else if (this._zoom > this._map._zoom) { //Zoom out, merge
			this._highestZoom = Math.min(this._highestZoom, this._map._zoom);

			//Ensure all of the intermediate zoom levels are generated
			var now;
			while (this._zoom > this._map._zoom) {
				now = this._markersAndClustersAtZoom[this._zoom];
				newState = this._markersAndClustersAtZoom[this._zoom - 1];
				this._zoom--;

				if (!newState) {
					newState = this._cluster([], [], now.clusters.concat(now.unclustered), this._zoom);
					this._markersAndClustersAtZoom[this._zoom] = newState;
				}
			}

			this._animationZoomOut(newState.clusters, newState.unclustered, depth);
		}
	},

	addLayer: function (layer) {
		if (!this._map) {
			this._needsClustering.push(layer);
			return this;
		}


		//TODO: If we have already clustered we'll need to add this one to a cluster
		//Should be able to use _cluster with the current clusters and just this layer
		L.FeatureGroup.prototype.addLayer.call(this, layer);

		var zoom = this._map._zoom,
		    current = this._markersAndClustersAtZoom[zoom],
		    newClusters = this._cluster(current.clusters, current.unclustered, [layer], zoom);

		//TODO: At the moment we blow away all other zoom levels, but really we could probably get away with not doing that
		this._highestZoom = this._zoom = zoom;
		this._markersAndClustersAtZoom = {};
		this._markersAndClustersAtZoom[zoom] = newClusters;

		var bounds = this._getExpandedVisibleBounds();

		for (var i = 0; i < newClusters.clusters.length; i++) {
			var c = newClusters.clusters[i];

			//Flatten all child clusters as they are now wrong
			c._markers = c.getAllChildMarkers();
			c._childClusters = [];
		}

		var me = this;
		setTimeout(function () {
			me._animationStart();
			for (var j = 0; j < newClusters.clusters.length; j++) {
				var v = newClusters.clusters[j];
				if (v._icon) {
					v.setLatLng(v._latlng);
				}
			}
			me._animationZoomOut(newClusters.clusters, 1);
		}, 0);

		return this;
	},

	removeLayer: function (layer) {
		var current = this._markersAndClustersAtZoom[this._map._zoom],
			i = current.unclustered.indexOf(layer),
			killParents = false;

		//TODO: This whole thing could probably be better

		//Remove the marker
		L.FeatureGroup.prototype.removeLayer.call(this, layer);

		if (i !== -1) //Is unclustered at the current zoom level
		{
			current.unclustered.splice(i, 1);
			
			killParents = true; //Need to rebuild parents as they may be clusters just because this marker makes them one
		}
		else //it is a child of a cluster
		{
			//Find the cluster
			for (i = current.clusters.length - 1; i >= 0; i--) {
				var c = current.clusters[i];
				
				if (c._recursivelyRemoveChildMarker(layer)) {
					if (c._childCount == 1) {
						//Remove cluster and add individual marker
						L.FeatureGroup.prototype.removeLayer.call(this, c);
						L.FeatureGroup.prototype.addLayer.call(this, c._markers[0]);
						current.unclustered.push(c._markers[0]);
						killParents = true; //Need to rebuild parents as they could have references to this cluster
					}
					break;
				}
			}
		}

		if (killParents) {
			//blow away all parent levels as they are now wrong
			for (i in this._markersAndClustersAtZoom)
			{
				if (i > this._map._zoom) {
					delete this._markersAndClustersAtZoom[i];
				}
			}
		}

		return this;
	},

	onAdd: function (map) {
		L.FeatureGroup.prototype.onAdd.call(this, map); // LayerGroup

		this._generateInitialClusters();
		this._map.on('zoomend', this._zoomEnd, this);
		this._map.on('moveend', this._moveEnd, this);
	},

	//Takes a list of objects that have a 'getLatLng()' function (Marker / MarkerCluster)
	//Performs clustering on them (using a greedy algorithm) and returns those clusters.
	//Returns { clusters: [], unclustered: [] }
	_cluster: function (existingClusters, existingUnclustered, toCluster, zoom) {
		var clusterRadiusSqrd = this.options.maxClusterRadius * this.options.maxClusterRadius,
		    clusters = existingClusters,
		    unclustered = existingUnclustered,
		    i, j, c;

		//Calculate pixel positions
		for (i = 0; i < clusters.length; i++) {
			c = clusters[i];
			c._projCenter = this._map.project(c.getLatLng(), zoom);
		}
		for (i = 0; i < unclustered.length; i++) {
			c = unclustered[i];
			c._projCenter = this._map.project(c.getLatLng(), zoom);
		}
		for (i = 0; i < toCluster.length; i++) {
			c = toCluster[i];
			c._projCenter = this._map.project(c.getLatLng(), zoom);
		}


		//go through each point
		for (i = 0; i < toCluster.length; i++) {
			var point = toCluster[i];

			var used = false;

			//try add it to an existing cluster
			for (j = 0; j < clusters.length; j++) {
				c = clusters[j];
				if (this._sqDist(point._projCenter, c._projCenter) <= clusterRadiusSqrd) {
					c._addChild(point);
					c._projCenter = this._map.project(c.getLatLng(), zoom);

					used = true;
					break;
				}
			}

			//otherwise, look through all of the markers we haven't managed to cluster and see if we should form a cluster with them
			if (!used) {
				for (j = 0 ; j < unclustered.length; j++) {
					if (this._sqDist(point._projCenter, unclustered[j]._projCenter) <= clusterRadiusSqrd) {
						//Create a new cluster with these 2
						var newCluster = new L.MarkerCluster(this, point, unclustered[j]);
						clusters.push(newCluster);

						newCluster._projCenter = this._map.project(newCluster.getLatLng(), zoom);

						unclustered.splice(j, 1);
						used = true;
						break;
					}
				}
				if (!used) {
					unclustered.push(point);
				}
			}
		}

		//Any clusters that did not end up being a child of a new cluster, make them a child of a new cluster
		for (i = unclustered.length - 1; i >= 0; i--) {
			c = unclustered[i];
			delete c._projCenter;

			if (c instanceof L.MarkerCluster) {
				var nc = new L.MarkerCluster(this, c);
				clusters.push(nc);
				unclustered.splice(i, 1);
			}
		}

		//Remove the _projCenter temp variable from clusters
		for (i = 0; i < clusters.length; i++) {
			delete clusters[i]._projCenter;
			clusters[i]._baseInit();
		}

		return { 'clusters': clusters, 'unclustered': unclustered };
	},

	//Gets the maps visible bounds expanded in each direction by the size of the screen (so the user cannot see an area we do not cover in one pan)
	_getExpandedVisibleBounds: function () {
		var map = this._map,
			bounds = map.getPixelBounds(),
			width = Math.abs(bounds.max.x - bounds.min.x),
			height = Math.abs(bounds.max.y - bounds.min.y),
			sw = map.unproject(new L.Point(bounds.min.x - width, bounds.min.y - height)),
			ne = map.unproject(new L.Point(bounds.max.x + width, bounds.max.y + height));
		
		return new L.LatLngBounds(sw, ne);
	}
});

L.MarkerClusterGroup.include(!L.DomUtil.TRANSITION ? {

	//Non Animated versions of everything
	_animationStart: function () {
		//Do nothing...
	},
	_animationZoomIn: function (startingClusters, startingUnclustered, depth) {
		var bounds = this._getExpandedVisibleBounds(),
			i, c;

		//Add all children of current clusters to map and remove those clusters from map
		for (i = 0; i < startingClusters.length; i++) {
			c = startingClusters[i];

			//Remove old cluster
			L.FeatureGroup.prototype.removeLayer.call(this, c); //TODO Animate

			c._recursivelyAddChildrenToMap(null, depth, bounds);
		}
		for (i = startingUnclustered.length - 1; i >= 0; i--) {
			c = startingUnclustered[i];
			if (!bounds.contains(c._latlng)) {
				L.FeatureGroup.prototype.removeLayer.call(this, c);
			}
		}
	},
	_animationZoomOut: function (newClusters, newUnclustered, depth) {
		var bounds = this._getExpandedVisibleBounds(),
			i;

		//Just add new and remove old from the map
		for (i = 0; i < newClusters.length; i++) {
			var cl = newClusters[i];

			if (bounds.contains(cl._latlng)) {
				cl._addToMap();
			}
			cl._recursivelyRemoveChildrenFromMap(depth);
		}
		//Add new markers
		for (i = newUnclustered.length - 1; i >= 0; i--) {
			var m = newUnclustered[i];
			if (bounds.contains(m._latlng)) {
				L.FeatureGroup.prototype.addLayer.call(this, m); //TODO Animate
			}
		}

	}
} : {

	//Animated versions here
	_animationStart: function () {
		this._map._mapPane.className += ' leaflet-cluster-anim';
	},
	_animationZoomIn: function (startingClusters, startingUnclustered, depth) {
		var me = this,
		    map = this._map,
		    bounds = this._getExpandedVisibleBounds(),
		    i, c, startPos;

		//Add all children of current clusters to map and remove those clusters from map
		for (i = 0; i < startingClusters.length; i++) {
			c = startingClusters[i];
			startPos = c.getLatLng();

			//Remove old cluster
			L.FeatureGroup.prototype.removeLayer.call(this, c); //TODO Animate

			c._recursivelyAddChildrenToMap(startPos, depth, bounds);
		}

		//Remove all markers that aren't visible any more
		for (i = startingUnclustered.length - 1; i >= 0; i--) {
			c = startingUnclustered[i];
			if (!bounds.contains(c._latlng)) {
				L.FeatureGroup.prototype.removeLayer.call(this, c);
			}
		}

		//Immediately fire an event to update the opacity (If we immediately set it they won't animate)
		setTimeout(function () {
			for (i in me._layers) {
				var n = me._layers[i];

				if (!(n instanceof L.MarkerCluster) && n._icon) {
					n.setOpacity(1);
				}
			}
		}, 0);

		this._inZoomAnimation++;
		//Start up a function to update the positions of the just added clusters/markers
		//This must happen after otherwise they don't get animated
		setTimeout(function () {

			for (var j = 0; j < startingClusters.length; j++) {
				startingClusters[j]._recursivelyRestoreChildPositions(depth);
			}

			setTimeout(function () {
				map._mapPane.className = map._mapPane.className.replace(' leaflet-cluster-anim', '');
				me._inZoomAnimation--;
			}, 250);
		}, 0);
	},
	_animationZoomOut: function (newClusters, newUnclustered, depth) {
		var map = this._map,
		    bounds = this._getExpandedVisibleBounds(),
		    i;

		//Animate all of the markers in the clusters to move to their cluster center point
		for ( i = 0; i < newClusters.length; i++) {
			var c = newClusters[i];

			c._recursivelyAnimateChildrenIn(this._map.latLngToLayerPoint(c.getLatLng()).round(), depth);

			if (bounds.contains(c._latlng)) { //Add the new cluster but have it be hidden (so it gets animated, display=none stops transition)
				c.setOpacity(0);
				c._addToMap();
			}
		}
		this._inZoomAnimation++;

		var me = this;

		//Immediately fire an event to update the opacity (If we immediately set it they won't animate)
		setTimeout(function () {
			for (i = 0; i < newClusters.length; i++) {
				var n = newClusters[i];

				if (n._icon) {
					n.setOpacity(1);
				}
			}
		}, 0);

		//TODO: Maybe use the transition timing stuff to make this more reliable
		setTimeout(function () {

			map._mapPane.className = map._mapPane.className.replace(' leaflet-cluster-anim', '');
			for (i = 0; i < newClusters.length; i++) {
				var cl = newClusters[i];
				cl._recursivelyRemoveChildrenFromMap(depth);
			}
			for (i = newUnclustered.length - 1; i >= 0; i--) {
				var m = newUnclustered[i];
				if (bounds.contains(m._latlng)) {
					L.FeatureGroup.prototype.addLayer.call(me, m);
				}
			}
			me._inZoomAnimation--;
		}, 250);
	}
});