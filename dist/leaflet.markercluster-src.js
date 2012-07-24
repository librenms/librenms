/*
 Copyright (c) 2012, Smartrak, David Leaver
 Leaflet.markercluster is an open-source JavaScript library for Marker Clustering on leaflet powered maps.
 https://github.com/danzel/Leaflet.markercluster
*/
(function (window, undefined) {

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


/*
 * L.MarkerClusterGroup extends L.FeatureGroup by clustering the markers contained within
 */

L.MarkerClusterGroup = L.FeatureGroup.extend({

	options: {
		maxClusterRadius: 60, //A cluster will cover at most this many pixels from its center
		iconCreateFunction: L.MarkerClusterDefault ? L.MarkerClusterDefault.iconCreateFunction : null
	},

	initialize: function (options) {
		L.Util.setOptions(this, options);

		L.FeatureGroup.prototype.initialize.call(this, []);

		this._inZoomAnimation = 0;
		this._needsClustering = [];
		//The bounds of the currently shown area (from _getExpandedVisibleBounds) Updated on zoom/move
		this._currentShownBounds = null;
	},

	//Overrides FeatureGroup._propagateEvent
	_propagateEvent: function (e) {
		if (e.target instanceof L.MarkerCluster) {
			e.type = 'cluster' + e.type;
		}
		L.FeatureGroup.prototype._propagateEvent.call(this, e);
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
		var minZoom = this._map.getMinZoom(),
			maxZoom = this._map.getMaxZoom(),
			currentZoom = this._map.getZoom();

		this._topClusterLevel = this._clusterToMarkerCluster(this._needsClustering, maxZoom);

		//Generate to the top
		while (minZoom < this._topClusterLevel._zoom) {
			this._topClusterLevel = this._clusterToMarkerCluster(this._topClusterLevel._childClusters.concat(this._topClusterLevel._markers), this._topClusterLevel._zoom - 1);
		}

		//Remember the current zoom level and bounds
		this._zoom = currentZoom;
		this._currentShownBounds = this._getExpandedVisibleBounds();

		//Make things appear on the map
		this._topClusterLevel._recursivelyAddChildrenToMap(null, currentZoom - minZoom + 1, this._currentShownBounds);
	},

	//Merge and split any existing clusters that are too big or small
	_mergeSplitClusters: function () {

		if (this._zoom < this._map._zoom) { //Zoom in, split
			//Remove clusters now off screen
			this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, this._zoom - this._topClusterLevel._zoom, this._getExpandedVisibleBounds());

			this._animationZoomIn(this._zoom, this._map._zoom);

		} else if (this._zoom > this._map._zoom) { //Zoom out, merge

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

		if (this._spiderfierOnAdd) { //TODO FIXME: Not sure how to have spiderfier add something on here nicely
			this._spiderfierOnAdd();
		}
	},

	//Takes a list of markers and clusters the new marker in to them
	//Will return null or the new MarkerCluster. The clustered in marker is removed from the given array
	_clusterOne: function (unclusteredMarkers, newMarker, zoom) {
		var markerPos = newMarker._projCenter || this._map.project(newMarker.getLatLng(), zoom),
			clusterDiameterSqrd = 2 * this.options.maxClusterRadius * 2 * this.options.maxClusterRadius,
			i, m, mPos;

		for (i = unclusteredMarkers.length - 1; i >= 0; i--) {
			m = unclusteredMarkers[i];
			mPos = m._projCenter || this._map.project(m.getLatLng(), zoom);

			if (this._sqDist(markerPos, mPos) <= clusterDiameterSqrd) {
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
	//toCluster: List of Markers/MarkerClusters to cluster
	//Returns { 'clusters': [new clusters], 'unclustered': [unclustered markers] }
	_cluster: function (toCluster, zoom) {
		var clusterRadiusSqrd = this.options.maxClusterRadius * this.options.maxClusterRadius,
		    clusters = [],
		    unclustered = [],
		    i, j, c;

		//go through each point
		for (i = toCluster.length - 1; i >= 0; i--) {
			var point = toCluster[i],
				used = false;

			point._projCenter = this._map.project(point.getLatLng(), zoom); //Calculate pixel position

			//try add it to an existing cluster
			for (j = clusters.length - 1; j >= 0; j--) {
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
		for (i = clusters.length - 1; i >= 0; i--) {
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
		if (newCluster !== layer && newCluster._childCount === 2) {
			newCluster._recursivelyRemoveChildrenFromMap(newCluster._bounds, 1);
		}
	}
} : {

	//Animated versions here
	_animationStart: function () {
		this._map._mapPane.className += ' leaflet-cluster-anim';
	},
	_animationEnd: function () {
		this._map._mapPane.className = this._map._mapPane.className.replace(' leaflet-cluster-anim', '');
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

			if (c._isSingleParent() && depthToDescend === 1) { //Immediately add the new child and remove us
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
			var j, n;

			//Update opacities
			me._topClusterLevel._recursivelyBecomeVisible(bounds, depthToStartAt + depthToDescend);
			//TODO Maybe? Update markers in _recursivelyBecomeVisible
			for (j in me._layers) {
				if (me._layers.hasOwnProperty(j)) {
					n = me._layers[j];

					if (!(n instanceof L.MarkerCluster) && n._icon) {
						n.setOpacity(1);
					}
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
		if (newCluster !== layer) {
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
			}
		}
	}
});

L.MarkerCluster = L.Marker.extend({
	initialize: function (group, a, b) {
		this._group = group;

		this._markers = [];
		this._childClusters = [];
		this._childCount = 0;

		this._bounds = new L.LatLngBounds();

		this._addChild(a);
		if (b) {
			this._addChild(b);
		}
	},

	//Recursively retrieve all child markers of this cluster
	getAllChildMarkers: function (storageArray) {
		storageArray = storageArray || [];

		for (var i = this._childClusters.length - 1; i >= 0; i--) {
			this._childClusters[i].getAllChildMarkers(storageArray);
		}

		for (var j = this._markers.length - 1; j >= 0; j--) {
			storageArray.push(this._markers[j]);
		}

		return storageArray;
	},

	//Returns the count of how many child markers we have
	getChildCount: function () {
		return this._childCount;
	},

	//Zoom to the extents of this cluster
	zoomToBounds: function () {
		this._group._map.fitBounds(this._bounds);
	},

	_baseInit: function () {
		L.Marker.prototype.initialize.call(this, this._latlng, { icon: this._group.options.iconCreateFunction(this._childCount) });
	},
	
	_addChild: function (new1) {
		if (new1 instanceof L.MarkerCluster) {
			this._childClusters.push(new1);
			this._childCount += new1._childCount;
		} else {
			this._markers.push(new1);
			this._childCount++;
		}

		if (this._icon) {
			this.setIcon(this._group.options.iconCreateFunction(this._childCount));
		}

		this._expandBounds(new1);
	},

	_expandBounds: function (marker) {

		if (marker instanceof L.MarkerCluster) {
			this._bounds.extend(marker._bounds);
		} else {
			this._bounds.extend(marker.getLatLng());
		}

		this._latlng = this._bounds.getCenter();
	},

	//Set our markers position as given and add it to the map
	_addToMap: function (startPos) {
		if (startPos) {
			this._backupLatlng = this._latlng;
			this.setLatLng(startPos);
		}
		L.FeatureGroup.prototype.addLayer.call(this._group, this);
	},

	//layer: The layer to try add
	//returns:
	//  true: was able to put this marker in, but don't know its current visible parents position
	//  false: wasn't able to put this marker in
	//  a Marker/MarkerCluster: the visible parent of the marker (or the marker itself if it should be visible)
	_recursivelyAddLayer: function (layer, zoom) {
		var result = false;

		for (var i = this._childClusters.length - 1; i >= 0; i--) {
			var c = this._childClusters[i];
			//Recurse into children where their bounds fits the layer or they can just take it
			if (c._bounds.contains(layer.getLatLng()) || c._canAcceptPosition(layer.getLatLng(), zoom + 1)) {
				result = c._recursivelyAddLayer(layer, zoom + 1);
				if (result) {
					this._childCount++;
					break;
				}
			}
		}

		//Couldn't add it to a child, but it should be part of us (this._zoom -> we are the root node)
		if (!result && (this._canAcceptPosition(layer.getLatLng(), zoom) || this._zoom)) {

			//Add to ourself instead
			result = this._group._clusterOne(this._markers, layer, zoom);

			if (result) {
				result._baseInit();
				this._addChild(result);
			} else {
				this._addChild(layer);
				result = true;
			}
		}

		if (result) {
			if (!this._zoom) {
				this.setIcon(this._group.options.iconCreateFunction(this._childCount));
			}
			this._recalculateBounds();
		}
		if (result === true) {
			if (this._icon) {
				result = this;
			} else if ((this._markers.length > 0 && this._markers[0]._icon) || (this._childClusters.length > 1 && this._childClusters[0]._icon)) {
				result = layer;
			}
		}

		return result;
	},

	_canAcceptPosition: function (latlng, zoom) {
		var clusterRadiusSqrd = this._group.options.maxClusterRadius * this._group.options.maxClusterRadius,
			pos = this._group._map.project(this._latlng, zoom),
			otherpos = this._group._map.project(latlng, zoom);

		return (this._group._sqDist(pos, otherpos) <= clusterRadiusSqrd);
	},

	//Removes the given node from this marker cluster (or its child as required)
	//Returns true if it (or a child cluster) removes the marker
	_recursivelyRemoveLayer: function (layer) {
		var group = this._group,
			markers = this._markers,
			childClusters = this._childClusters,
			i;

		//Check our children
		for (i = markers.length - 1; i >= 0; i--) {
			if (markers[i] === layer) {
				if (markers[i]._icon) {
					L.FeatureGroup.prototype.removeLayer.call(group, markers[i]);
				}

				markers.splice(i, 1);
				this._recalculateBounds();
		
				this._childCount--;
				if (this._icon) {
					this.setIcon(group.options.iconCreateFunction(this._childCount));
				}
				return true;
			}
		}

		//Otherwise check our childClusters
		for (i = childClusters.length - 1; i >= 0; i--) {
			var child = childClusters[i];

			if (child._bounds.contains(layer._latlng) && child._recursivelyRemoveLayer(layer)) {
				this._childCount--;

				//if our child cluster is no longer a cluster, remove it and replace with just the marker
				if (child._childCount === 1) {

					//If the child is visible, remove it and put the marker on the map
					if (child._icon) {
						L.FeatureGroup.prototype.removeLayer.call(group, child);
						L.FeatureGroup.prototype.addLayer.call(group, child._markers[0]);
					}
					
					//Take ownership of its only marker and bin the cluster
					markers.push(child._markers[0]);
					childClusters.splice(i, 1);
				}

				this._recalculateBounds();

				if (this._icon && this._childCount > 1) { //No need to update if we are getting removed anyway
					this.setIcon(group.options.iconCreateFunction(this._childCount));
				}
				return true;
			}
		}

		return false;
	},

	_recursivelyAnimateChildrenIn: function (bounds, center, depth) {
		this._recursively(bounds, 0, depth - 1,
			function (c) {
				var markers = c._markers,
					i, m;
				for (i = markers.length - 1; i >= 0; i--) {
					m = markers[i];

					//Only do it if the icon is still on the map
					if (m._icon) {
						m._setPos(center);
						m.setOpacity(0);
					}
				}
			},
			function (c) {
				var childClusters = c._childClusters,
					j, cm;
				for (j = childClusters.length - 1; j >= 0; j--) {
					cm = childClusters[j];
					if (cm._icon) {
						cm._setPos(center);
						cm.setOpacity(0);
					}
				}
			}
		);
	},

	_recursivelyAnimateChildrenInAndAddSelfToMap: function (bounds, depthToStartAt, depthToAnimateIn) {
		this._recursively(bounds, depthToStartAt, 0,
			function (c) {
				c._recursivelyAnimateChildrenIn(bounds, c._group._map.latLngToLayerPoint(c.getLatLng()).round(), depthToAnimateIn);

				//TODO: depthToAnimateIn affects _isSingleParent, if there is a multizoom we may/may not be.
				//As a hack we only do a animation free zoom on a single level zoom, if someone does multiple levels then we always animate
				if (c._isSingleParent() && depthToAnimateIn === 1) {
					c.setOpacity(1);
					c._recursivelyRemoveChildrenFromMap(bounds, depthToAnimateIn - 1); //Immediately remove our children as we are replacing them. TODO previousBounds not bounds
				} else {
					c.setOpacity(0);
				}

				c._addToMap();
			}
		);
	},

	_recursivelyBecomeVisible: function (bounds, depth) {
		this._recursively(bounds, 0, depth, null, function (c) {
			c.setOpacity(1);
		});
	},

	_recursivelyAddChildrenToMap: function (startPos, depth, bounds) {
		this._recursively(bounds, 0, depth,
			function (c, recursionDepth) {
				if (recursionDepth === 0) {
					return;
				}

				//Add our child markers at startPos (so they can be animated out)
				for (var i = c._markers.length - 1; i >= 0; i--) {
					var nm = c._markers[i];

					if (!bounds.contains(nm._latlng)) {
						continue;
					}

					if (startPos) {
						nm._backupLatlng = nm.getLatLng();

						nm.setLatLng(startPos);
						nm.setOpacity(0);
					}

					L.FeatureGroup.prototype.addLayer.call(c._group, nm);
				}
			},
			function (c) {
				c._addToMap(startPos);

			}
		);
	},

	_recursivelyRestoreChildPositions: function (depth) {
		//Fix positions of child markers
		for (var i = this._markers.length - 1; i >= 0; i--) {
			var nm = this._markers[i];
			if (nm._backupLatlng) {
				nm.setLatLng(nm._backupLatlng);
				delete nm._backupLatlng;
			}
		}

		if (depth === 1) {
			//Reposition child clusters
			for (var j = this._childClusters.length - 1; j >= 0; j--) {
				this._childClusters[j]._restorePosition();
			}
		} else {
			for (var k = this._childClusters.length - 1; k >= 0; k--) {
				this._childClusters[k]._recursivelyRestoreChildPositions(depth - 1);
			}
		}
	},

	_restorePosition: function () {
		if (this._backupLatlng) {
			this.setLatLng(this._backupLatlng);
			delete this._backupLatlng;
		}
	},
	
	//exceptBounds: If set, don't remove any markers/clusters in it
	_recursivelyRemoveChildrenFromMap: function (previousBounds, depth, exceptBounds) {
		var m, i;
		this._recursively(previousBounds, 0, depth,
			function (c) {
				//Remove markers at every level
				for (i = c._markers.length - 1; i >= 0; i--) {
					m = c._markers[i];
					if (!exceptBounds || !exceptBounds.contains(m._latlng)) {
						L.FeatureGroup.prototype.removeLayer.call(c._group, m);
						m.setOpacity(1);
					}
				}
			},
			function (c) {
				//Remove child clusters at just the bottom level
				for (i = c._childClusters.length - 1; i >= 0; i--) {
					m = c._childClusters[i];
					if (!exceptBounds || !exceptBounds.contains(m._latlng)) {
						L.FeatureGroup.prototype.removeLayer.call(c._group, m);
						m.setOpacity(1);
					}
				}
			}
		);
	},

	//Run the given functions recursively to this and child clusters
	// boundsToApplyTo: a L.LatLngBounds representing the bounds of what clusters to recurse in to
	// depthToStartAt: the depth to start calling the given functions
	// timesToRecurse: how many layers deep to recurse in to after hitting depthToStartAt, bottom level: depthToRunFor == 0
	// runAtEveryLevel: function that takes an L.MarkerCluster as an argument that should be applied on every level
	// runAtBottomLevel: function that takes an L.MarkerCluster as an argument that should be applied at only the bottom level
	_recursively: function (boundsToApplyTo, depthToStartAt, timesToRecurse, runAtEveryLevel, runAtBottomLevel) {
		var childClusters = this._childClusters,
			i, c;

		if (depthToStartAt > 0) { //Still going down to required depth, just recurse to child clusters
			for (i = childClusters.length - 1; i >= 0; i--) {
				c = childClusters[i];
				if (boundsToApplyTo.intersects(c._bounds)) {
					c._recursively(boundsToApplyTo, depthToStartAt - 1, timesToRecurse, runAtEveryLevel, runAtBottomLevel);
				}
			}
		} else { //In required depth

			if (runAtEveryLevel) {
				runAtEveryLevel(this, timesToRecurse);
			}
			if (timesToRecurse === 0 && runAtBottomLevel) {
				runAtBottomLevel(this);
			}

			//TODO: This loop is almost the same as above
			if (timesToRecurse > 0) {
				for (i = childClusters.length - 1; i >= 0; i--) {
					c = childClusters[i];
					if (boundsToApplyTo.intersects(c._bounds)) {
						c._recursively(boundsToApplyTo, depthToStartAt, timesToRecurse - 1, runAtEveryLevel, runAtBottomLevel);
					}
				}
			}
		}
	},

	_recalculateBounds: function () {
		var markers = this._markers,
			childClusters = this._childClusters,
			i;

		this._bounds = new L.LatLngBounds();

		for (i = markers.length - 1; i >= 0; i--) {
			this._bounds.extend(markers[i].getLatLng());
		}
		for (i = childClusters.length - 1; i >= 0; i--) {
			this._bounds.extend(childClusters[i]._bounds);
		}

		this.setLatLng(this._bounds.getCenter());
	},


	//Returns true if we are the parent of only one cluster and that cluster is the same as us
	_isSingleParent: function () {
		//Don't need to check this._markers as the rest won't work if there are any
		return this._childClusters.length > 0 && this._childClusters[0]._childCount === this._childCount;
	}
});

/* Copyright (c) 2012 the authors listed at the following URL, and/or
the authors of referenced articles or incorporated external code:
http://en.literateprograms.org/Quickhull_(Javascript)?action=history&offset=20120410175256

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Retrieved from: http://en.literateprograms.org/Quickhull_(Javascript)?oldid=18434
*/

(function () {
	L.QuickHull = {
		getDistant: function (cpt, bl) {
			var vY = bl[1].lat - bl[0].lat,
				vX = bl[0].lng - bl[1].lng;
			return (vX * (cpt.lat - bl[0].lat) + vY * (cpt.lng - bl[0].lng));
		},


		findMostDistantPointFromBaseLine: function (baseLine, latLngs) {
			var maxD = 0,
				maxPt = null,
				newPoints = [],
				i, pt, d;

			for (i = latLngs.length - 1; i >= 0; i--) {
				pt = latLngs[i];
				d = this.getDistant(pt, baseLine);

				if (d > 0) {
					newPoints.push(pt);
				} else {
					continue;
				}

				if (d > maxD) {
					maxD = d;
					maxPt = pt;
				}

			}
			return { 'maxPoint': maxPt, 'newPoints': newPoints };
		},

		buildConvexHull: function (baseLine, latLngs) {
			var convexHullBaseLines = [],
				t = this.findMostDistantPointFromBaseLine(baseLine, latLngs);

			if (t.maxPoint) { // if there is still a point "outside" the base line
				convexHullBaseLines =
					convexHullBaseLines.concat(
						this.buildConvexHull([baseLine[0], t.maxPoint], t.newPoints)
					);
				convexHullBaseLines =
					convexHullBaseLines.concat(
						this.buildConvexHull([t.maxPoint, baseLine[1]], t.newPoints)
					);
				return convexHullBaseLines;
			} else {  // if there is no more point "outside" the base line, the current base line is part of the convex hull
				return [baseLine];
			}
		},

		getConvexHull: function (latLngs) {
			//find first baseline
			var maxLat = false, minLat = false,
				maxPt = null, minPt = null,
				i;

			for (i = latLngs.length - 1; i >= 0; i--) {
				var pt = latLngs[i];
				if (maxLat === false || pt.lat > maxLat) {
					maxPt = pt;
					maxLat = pt.lat;
				}
				if (minLat === false || pt.lat < minLat) {
					minPt = pt;
					minLat = pt.lat;
				}
			}
			var ch = [].concat(this.buildConvexHull([minPt, maxPt], latLngs),
								this.buildConvexHull([maxPt, minPt], latLngs));
			return ch;
		}
	};
}());

L.MarkerCluster.include({
	getConvexHull: function () {
		var childMarkers = this.getAllChildMarkers(),
			points = [],
			hullLatLng = [],
			hull, p, i;

		for (i = childMarkers.length - 1; i >= 0; i--) {
			p = childMarkers[i].getLatLng();
			points.push(p);
		}

		hull = L.QuickHull.getConvexHull(points);

		for (i = hull.length - 1; i >= 0; i--) {
			hullLatLng.push(hull[i][0]);
		}

		return hullLatLng;
	}
});

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



}(this));