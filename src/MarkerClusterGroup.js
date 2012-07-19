
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
		//The bounds of the currently shown area (from _getExpandedVisibleBounds) Updated on zoom/move
		this._currentShownBounds = null;
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
		this._currentShownBounds = this._getExpandedVisibleBounds();
	},

	_moveEnd: function () {
		if (this._inZoomAnimation > 0) {
			return;
		}
		return; //FIXME OBV
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

		this._currentShownBounds = bounds;
	},

	_generateInitialClusters: function () {
		console.log('generating initial topCluster ' + this._map.getZoom());
		var res = this._topClusterLevel = this._clusterToMarkerCluster([], [], this._needsClustering, this._map.getZoom());

		//Remember the current zoom level and bounds
		this._zoom = this._map._zoom;
		this._currentShownBounds = this._getExpandedVisibleBounds();

		//Make things appear on the map
		res._recursivelyAddChildrenToMap(null, 1, this._getExpandedVisibleBounds());
	},

	//Merge and split any existing clusters that are too big or small
	_mergeSplitClusters: function () {

		if (this._zoom < this._map._zoom) { //Zoom in, split

			//Clusters generate new children as needed on a zoom in

			this._animationZoomIn(this._zoom, this._map._zoom);

		} else if (this._zoom > this._map._zoom) { //Zoom out, merge

			//Ensure all of the intermediate zoom levels are generated, generating up happens outside of MarkerCluster
			while (this._topClusterLevel._zoom > this._map._zoom) {
				console.log('generating new topCluster for ' + (this._topClusterLevel._zoom - 1));
				this._topClusterLevel = this._clusterToMarkerCluster([], [], this._topClusterLevel._childClusters.concat(this._topClusterLevel._markers), this._topClusterLevel._zoom - 1);
				this._topClusterLevel = this._clusterToMarkerCluster([], [], this._topClusterLevel._childClusters.concat(this._topClusterLevel._markers), this._topClusterLevel._zoom - 1);
			}

			this._animationZoomOut(this._zoom, this._map._zoom);
		}
	},

	addLayer: function (layer) {
		if (!this._map) {
			this._needsClustering.push(layer);
			return this;
		}

		var me = this,
			position;

		//If we have already clustered we'll need to add this one to a cluster
		L.FeatureGroup.prototype.addLayer.call(this, layer); //TODO: If not animated maybe don't add it yet

		position = this._topClusterLevel._recursivelyAddChildMarker(layer);

		if (position) {
			//TODO Tidy up
			//this._animateSingleMarkerIn(layer, position);
			setTimeout(function () {
				me._animationStart.call(me);

				var backupLatlng = layer.getLatLng();

				layer.setLatLng(position);

				setTimeout(function () {
					L.FeatureGroup.prototype.removeLayer.call(me, layer);
					layer.setLatLng(backupLatlng);

					//HACKS
					map._mapPane.className = map._mapPane.className.replace(' leaflet-cluster-anim', ''); me._inZoomAnimation--;
				}, 250);
			}, 0);
		} else {
			//Do nothing, marker should be shown on map at own position at this level
		}

		return this;
	},

	removeLayer: function (layer) {
		var current = this._markersAndClustersAtZoom[this._map._zoom],
			i = current.unclustered.indexOf(layer),
			killParents = false;

		//TODO: This whole thing could probably be better

		//Remove the marker
		L.FeatureGroup.prototype.removeLayer.call(this, layer);

		if (i !== -1) { //Is unclustered at the current zoom level
			current.unclustered.splice(i, 1);
			
			killParents = true; //Need to rebuild parents as they may be clusters just because this marker makes them one
		} else { //it is a child of a cluster
			//Find the cluster
			for (i = current.clusters.length - 1; i >= 0; i--) {
				var c = current.clusters[i];
				
				if (c._recursivelyRemoveChildMarker(layer)) {
					if (c._childCount == 1) {
						//Remove cluster and add individual marker
						L.FeatureGroup.prototype.removeLayer.call(this, c);
						var marker = c._markers[0];
						L.FeatureGroup.prototype.addLayer.call(this, marker);
						current.unclustered.push(marker);
						current.clusters.splice(i, 1);
						killParents = true; //Need to rebuild parents as they could have references to this cluster
					}
					console.log('remaining clusters ' + current.clusters.length);
					console.log('remaining markers' + current.unclustered.length);
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
	//Returns a L.MarkerCluster with _zoom set
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

	//Clusters the given markers (with _cluster) and returns the result as a MarkerCluster
	_clusterToMarkerCluster: function (existingClusters, existingUnclustered, toCluster, zoom) {
		var res = this._cluster(existingClusters, existingUnclustered, toCluster, zoom),
			toAdd = res.clusters.concat(res.unclustered),
			result = new L.MarkerCluster(this, toAdd[0]),
			i;

		for (i = toAdd.length - 1; i > 0; i--) {
			result._addChild(toAdd[i]);
		}
		console.log('generated clusters: ' + result._childClusters.length + ", markers: " + result._markers.length + " at " + zoom);
		result._zoom = zoom;
		result._haveGeneratedChildClusters = true;
		return result;
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
	_animationZoomIn: function (previousZoomLevel, newZoomLevel) {
		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, previousZoomLevel - this._topClusterLevel._zoom);
		this._topClusterLevel._recursivelyAddChildrenToMap(null, newZoomLevel - this._topClusterLevel._zoom + 1, this._getExpandedVisibleBounds());
	},
	_animationZoomOut: function (previousZoomLevel, newZoomLevel) {
		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, previousZoomLevel - this._topClusterLevel._zoom);
		this._topClusterLevel._recursivelyAddChildrenToMap(null, newZoomLevel - this._topClusterLevel._zoom + 1, this._getExpandedVisibleBounds());
	}
} : {

	//Animated versions here
	_animationStart: function () {
		this._map._mapPane.className += ' leaflet-cluster-anim';
	},
	_animationZoomIn: function (previousZoomLevel, newZoomLevel) {
		var me = this,
		    map = this._map,
		    bounds = this._getExpandedVisibleBounds(),
		    i, 
		    depthToStartAt = 1 + previousZoomLevel - this._topClusterLevel._zoom,
		    depthToDescend = newZoomLevel - previousZoomLevel;
		console.log('animationZoomIn ' + depthToStartAt + ' ' + depthToDescend);

		//Add all children of current clusters to map and remove those clusters from map
		this._topClusterLevel._recursively(bounds, depthToStartAt, 0, function (c) {
			var startPos = c._latlng,
				markers = c._markers,
				m;

			if (c._isSingleParent() && depthToDescend == 1) { //Immediately add the new child and remove us
				L.FeatureGroup.prototype.removeLayer.call(me, c);
				c._recursivelyAddChildrenToMap(null, depthToDescend, bounds);
			} else {
				//Fade out old cluster
				c.setOpacity(0);
				c._recursivelyAddChildrenToMap(startPos, depthToDescend, bounds);
			}

			//Remove all markers that aren't visible any more
			//TODO: Do we actually need to do this on the higher levels too?
			for (i = markers.length - 1; i >= 0; i--) {
				m = markers[i];
				if (!bounds.contains(m._latlng)) {
					L.FeatureGroup.prototype.removeLayer.call(me, m);
				}
			}

		});

		//Immediately fire an event to update the opacity and locations (If we immediately set it they won't animate)
		setTimeout(function () {
			//Update opacities
			for (i in me._layers) {
				var n = me._layers[i];

				if (!(n instanceof L.MarkerCluster) && n._icon) {
					n.setOpacity(1);
				}
			}

			//update the positions of the just added clusters/markers
			me._topClusterLevel._recursively(bounds, depthToStartAt, 0, function (c) {
				c._recursivelyRestoreChildPositions(depthToDescend);
			});
		}, 0);

		this._inZoomAnimation++;

		//Remove the old clusters and close the zoom animation
		
		setTimeout(function () {
			//update the positions of the just added clusters/markers
			me._topClusterLevel._recursively(bounds, depthToStartAt, 0, function (c) {
				L.FeatureGroup.prototype.removeLayer.call(me, c);
			});

			map._mapPane.className = map._mapPane.className.replace(' leaflet-cluster-anim', '');
			me._inZoomAnimation--;
		}, 250);
	},
	_animationZoomOut: function (previousZoomLevel, newZoomLevel) {
		var map = this._map,
		    bounds = this._getExpandedVisibleBounds(),
		    depthToStartAt = 1 + newZoomLevel - this._topClusterLevel._zoom,
		    depthToAnimateIn = previousZoomLevel - newZoomLevel;

		console.log('animationZoomOut ' + depthToStartAt + ' ' + depthToAnimateIn);

		//Animate all of the markers in the clusters to move to their cluster center point
		this._topClusterLevel._recursivelyAnimateChildrenInAndAddSelfToMap(bounds, depthToStartAt, depthToAnimateIn);

		this._inZoomAnimation++;

		var me = this;

		//Immediately fire an event to update the opacity (If we immediately set it they won't animate)
		setTimeout(function () {
			me._topClusterLevel._recursivelyBecomeVisible(bounds, depthToStartAt);
		}, 0);

		//TODO: Maybe use the transition timing stuff to make this more reliable
		//When the animations are done, tidy up
		setTimeout(function () {

			map._mapPane.className = map._mapPane.className.replace(' leaflet-cluster-anim', '');
			me._topClusterLevel._recursively(bounds, depthToStartAt, 0, null, function (c) {
				c._recursivelyRemoveChildrenFromMap(bounds, depthToAnimateIn - 1);
			});
			me._inZoomAnimation--;
		}, 250);
	}
});