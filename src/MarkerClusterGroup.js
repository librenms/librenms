
/*
 * L.MarkerClusterGroup extends L.FeatureGroup by clustering the markers contained within
 */

L.MarkerClusterGroup = L.FeatureGroup.extend({

	options: {
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

		var newBounds = this._getExpandedVisibleBounds(),
			depth = this._zoom - this._topClusterLevel._zoom;

		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, depth, newBounds);
		this._topClusterLevel._recursivelyAddChildrenToMap(null, depth + 1, newBounds);

		this._currentShownBounds = newBounds;
		return;
	},

	_generateInitialClusters: function () {
		var res = this._topClusterLevel = this._clusterToMarkerCluster(this._needsClustering, this._map.getZoom()),
			minZoom = this._map.getMinZoom();

		//Generate 2 levels up if we can
		if (minZoom < this._topClusterLevel._zoom && this._topClusterLevel._childCount > 1) {
			this._topClusterLevel = this._clusterToMarkerCluster(this._topClusterLevel._childClusters.concat(this._topClusterLevel._markers), this._topClusterLevel._zoom - 1);
			if (minZoom < this._topClusterLevel._zoom && this._topClusterLevel._childCount > 1) {
				this._topClusterLevel = this._clusterToMarkerCluster(this._topClusterLevel._childClusters.concat(this._topClusterLevel._markers), this._topClusterLevel._zoom - 1);
			}
		}

		//Remember the current zoom level and bounds
		this._zoom = this._map._zoom;
		this._currentShownBounds = this._getExpandedVisibleBounds();

		//Make things appear on the map
		res._recursivelyAddChildrenToMap(null, 1, this._getExpandedVisibleBounds());
	},

	//Merge and split any existing clusters that are too big or small
	_mergeSplitClusters: function () {

		if (this._zoom < this._map._zoom) { //Zoom in, split
			//Note: Clusters generate new children as needed on a zoom in

			//Remove clusters now off screen
			this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, this._zoom - this._topClusterLevel._zoom, this._getExpandedVisibleBounds());

			this._animationZoomIn(this._zoom, this._map._zoom);

		} else if (this._zoom > this._map._zoom) { //Zoom out, merge

			//Ensure all of the intermediate zoom levels are generated, generating up happens outside of MarkerCluster
			//We also try keep 2 more levels on top if we can so the tree is used more efficiently
			while (this._topClusterLevel._zoom > Math.max(this._map.getMinZoom(), this._map._zoom - 2)) {
				this._topClusterLevel = this._clusterToMarkerCluster(this._topClusterLevel._childClusters.concat(this._topClusterLevel._markers), this._topClusterLevel._zoom - 1);
			}

			this._animationZoomOut(this._zoom, this._map._zoom);
		}
	},

	addLayer: function (layer) {
		if (!this._map) {
			this._needsClustering.push(layer);
			return this;
		}

		//If we have already clustered we'll need to add this one to a cluster

		var newCluster = this._topClusterLevel._recursivelyAddLayer(layer, this._topClusterLevel._zoom - 1);

		this._animationAddLayer(layer, newCluster);

		return this;
	},

	removeLayer: function (layer) {
		this._topClusterLevel._recursivelyRemoveLayer(layer);

		return this;
	},

	onAdd: function (map) {
		L.FeatureGroup.prototype.onAdd.call(this, map); // LayerGroup

		this._generateInitialClusters();
		this._map.on('zoomend', this._zoomEnd, this);
		this._map.on('moveend', this._moveEnd, this);
	},

	//Takes a list of markers and clusters the new marker in to them
	//Will return null or the new MarkerCluster. The clustered in marker is removed from the given array
	_clusterOne: function (unclusteredMarkers, newMarker, zoom) {
		var markerPos = newMarker._projCenter || this._map.project(newMarker.getLatLng(), zoom),
			clusterRadiusSqrd = this.options.maxClusterRadius * this.options.maxClusterRadius,
			i, m, mPos;

		for (i = unclusteredMarkers.length - 1; i >= 0; i--) {
			m = unclusteredMarkers[i];
			mPos = m._projCenter || this._map.project(m.getLatLng(), zoom);

			if (this._sqDist(markerPos, mPos) <= clusterRadiusSqrd) {
				//Create a new cluster with these 2
				var newCluster = new L.MarkerCluster(this, m, newMarker);

				unclusteredMarkers.splice(i, 1);
				return newCluster;
			}
		}

		return null;
	},

	//Takes a list of objects that have a 'getLatLng()' function (Marker / MarkerCluster)
	//Performs clustering on them (using a greedy algorithm) and returns those clusters.
	//toCluster: List of Markers/MarkerClusters to cluster. MarkerClusters MUST be first in the list
	//Returns { 'clusters': [new clusters], 'unclustered': [unclustered markers] }
	_cluster: function (toCluster, zoom) {
		var clusterRadiusSqrd = this.options.maxClusterRadius * this.options.maxClusterRadius,
		    clusters = [],
		    unclustered = [],
		    hasChildClusters = (toCluster.length > 0 && toCluster[0] instanceof L.MarkerCluster),
		    i, j, c;

		//go through each point
		for (i = 0; i < toCluster.length; i++) {
			var point = toCluster[i],
				used = false;

			point._projCenter = this._map.project(point.getLatLng(), zoom); //Calculate pixel position

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
				var newCluster = this._clusterOne(unclustered, point);
				if (newCluster) {
					newCluster._haveGeneratedChildClusters = hasChildClusters;
					if (!hasChildClusters) {
						newCluster._zoomForCluster = zoom + 1;
					}
					newCluster._projCenter = this._map.project(newCluster.getLatLng(), zoom);
					clusters.push(newCluster);
				} else {
					//Didn't manage to use it
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
				nc._haveGeneratedChildClusters = true;
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
	_clusterToMarkerCluster: function (toCluster, zoom) {
		var res = this._cluster(toCluster, zoom),
			toAdd = res.clusters.concat(res.unclustered),
			result = new L.MarkerCluster(this, toAdd[0]),
			i;

		for (i = toAdd.length - 1; i > 0; i--) {
			result._addChild(toAdd[i]);
		}
		result._zoom = zoom;
		result._haveGeneratedChildClusters = true;
		return result;
	},

	//Gets the maps visible bounds expanded in each direction by the size of the screen (so the user cannot see an area we do not cover in one pan)
	_getExpandedVisibleBounds: function () {
		var map = this._map,
			bounds = map.getPixelBounds(),
			width =  Math.abs(bounds.max.x - bounds.min.x),
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
	},
	_animationAddLayer: function (layer, newCluster) {
		L.FeatureGroup.prototype.addLayer.call(this, newCluster);
		if (newCluster != layer && newCluster._childCount == 2) {
			newCluster._recursivelyRemoveChildrenFromMap(newCluster._bounds, 1);
		}
	}
} : {

	//Animated versions here
	_animationStart: function () {
		this._map._mapPane.className += ' leaflet-cluster-anim';
	},
	_animationEnd: function () {
		this._map._mapPane.className = map._mapPane.className.replace(' leaflet-cluster-anim', '');
		this._inZoomAnimation--;
	},
	_animationZoomIn: function (previousZoomLevel, newZoomLevel) {
		var me = this,
		    bounds = this._getExpandedVisibleBounds(),
		    i, 
		    depthToStartAt = 1 + previousZoomLevel - this._topClusterLevel._zoom,
		    depthToDescend = newZoomLevel - previousZoomLevel;

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

			me._animationEnd();
		}, 250);
	},

	_animationZoomOut: function (previousZoomLevel, newZoomLevel) {
		var depthToStartAt = 1 + newZoomLevel - this._topClusterLevel._zoom,
		    depthToAnimateIn = previousZoomLevel - newZoomLevel;

		this._animationZoomOutSingle(this._topClusterLevel, depthToStartAt, depthToAnimateIn);

		//Need to add markers for those that weren't on the map before but are now
		this._topClusterLevel._recursivelyAddChildrenToMap(null, depthToStartAt, this._getExpandedVisibleBounds());
	},
	_animationZoomOutSingle: function (marker, depthToStartAt, depthToAnimateIn) {
		var bounds = this._getExpandedVisibleBounds();

		//Animate all of the markers in the clusters to move to their cluster center point
		marker._recursivelyAnimateChildrenInAndAddSelfToMap(bounds, depthToStartAt, depthToAnimateIn);

		this._inZoomAnimation++;

		var me = this;

		//Immediately fire an event to update the opacity (If we immediately set it they won't animate)
		setTimeout(function () {
			marker._recursivelyBecomeVisible(bounds, depthToStartAt);
		}, 0);

		//TODO: Maybe use the transition timing stuff to make this more reliable
		//When the animations are done, tidy up
		setTimeout(function () {

			marker._recursively(bounds, depthToStartAt, 0, null, function (c) {
				c._recursivelyRemoveChildrenFromMap(bounds, depthToAnimateIn - 1);
			});
			me._animationEnd();
		}, 250);
	},
	_animationAddLayer: function (layer, newCluster) {
		var me = this;

		L.FeatureGroup.prototype.addLayer.call(this, layer);
		if (newCluster != layer) {
			if (newCluster._childCount > 2) { //Was already a cluster

				this._animationStart();
				setTimeout(function () {


					var backupLatlng = layer.getLatLng();
					layer.setLatLng(newCluster._latlng);
					layer.setOpacity(0);

					setTimeout(function () {
						L.FeatureGroup.prototype.removeLayer.call(me, layer);
						layer.setLatLng(backupLatlng);

						me._animationEnd();
					}, 250);
				}, 0);

			} else { //Just became a cluster
				setTimeout(function () {
					me._animationStart();
					me._animationZoomOutSingle(newCluster, 0, 1);
				}, 0);
				//newCluster._recursivelyAnimateChildrenInAndAddSelfToMap(newCluster._bounds, 0, 1);
			}
		}
	}
});