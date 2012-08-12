/*
 Copyright (c) 2012, Smartrak, David Leaver
 Leaflet.markercluster is an open-source JavaScript library for Marker Clustering on leaflet powered maps.
 https://github.com/danzel/Leaflet.markercluster
*/
(function (window, undefined) {


/*
 * L.MarkerClusterGroup extends L.FeatureGroup by clustering the markers contained within
 */

L.MarkerClusterGroup = L.FeatureGroup.extend({

	options: {
		maxClusterRadius: 80, //A cluster will cover at most this many pixels from its center
		iconCreateFunction: null,

		spiderfyOnMaxZoom: true,
		showCoverageOnHover: true,
		zoomToBoundsOnClick: true
	},

	initialize: function (options) {
		L.Util.setOptions(this, options);
		if (!this.options.iconCreateFunction) {
			this.options.iconCreateFunction = this._defaultIconCreateFunction;
		}

		L.FeatureGroup.prototype.initialize.call(this, []);

		this._inZoomAnimation = 0;
		this._needsClustering = [];
		//The bounds of the currently shown area (from _getExpandedVisibleBounds) Updated on zoom/move
		this._currentShownBounds = null;
	},

	addLayer: function (layer) {
		if (!this._map) {
			this._needsClustering.push(layer);
			return this;
		}

		//If we have already clustered we'll need to add this one to a cluster

		if (this._unspiderfy) {
			this._unspiderfy();
		}

		var newCluster = this._topClusterLevel._recursivelyAddLayer(layer, this._topClusterLevel._zoom - 1);

		this._animationAddLayer(layer, newCluster);

		return this;
	},

	removeLayer: function (layer) {
		if (this._unspiderfy) {
			this._unspiderfy();
			this._unspiderfyLayer(layer);
		}

		if (!this._topClusterLevel._recursivelyRemoveLayer(layer)) {
			//If this happens you are doing something bad
			//If you've moved a marker that is in the cluster then that would be why
			//console.log('failed to remove');
			var a = 0;
		}

		return this;
	},

	clearLayers: function () {
		//Need our own special implementation as the LayerGroup one doesn't work for us

		//If we aren't on the map yet, just blow away the markers we know of
		if (!this._map) {
			this._needsClustering = [];
			return this;
		}

		//Remove all the visible layers
		for (var i in this._layers) {
			if (this._layers.hasOwnProperty(i)) {
				L.FeatureGroup.prototype.removeLayer.call(this, this._layers[i]);
			}
		}

		//Reset _topClusterLevel
		this._generateInitialClusters();

		return this;
	},

	//Overrides FeatureGroup.onAdd
	onAdd: function (map) {
		L.FeatureGroup.prototype.onAdd.call(this, map);

		if (!this._topClusterLevel) {
			this._generateInitialClusters();
		}

		this._map.on('zoomend', this._zoomEnd, this);
		this._map.on('moveend', this._moveEnd, this);

		if (this._spiderfierOnAdd) { //TODO FIXME: Not sure how to have spiderfier add something on here nicely
			this._spiderfierOnAdd();
		}

		this._bindEvents();
	},

	//Overrides FeatureGroup.onRemove
	onRemove: function (map) {
		this._map.off('zoomend', this._zoomEnd, this);
		this._map.off('moveend', this._moveEnd, this);

		if (this._spiderfierOnRemove) { //TODO FIXME: Not sure how to have spiderfier add something on here nicely
			this._spiderfierOnRemove();
		}

		L.FeatureGroup.prototype.onRemove.call(this, map);
	},

	//Overrides FeatureGroup._propagateEvent
	_propagateEvent: function (e) {
		if (e.target instanceof L.MarkerCluster) {
			e.type = 'cluster' + e.type;
		}
		L.FeatureGroup.prototype._propagateEvent.call(this, e);
	},

	//Default functionality
	_defaultIconCreateFunction: function (childCount) {
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

	_bindEvents: function () {
		var shownPolygon = null,
			map = this._map,

			spiderfyOnMaxZoom = this.options.spiderfyOnMaxZoom,
			showCoverageOnHover = this.options.showCoverageOnHover,
			zoomToBoundsOnClick = this.options.zoomToBoundsOnClick;

		//Zoom on cluster click or spiderfy if we are at the lowest level
		if (spiderfyOnMaxZoom || zoomToBoundsOnClick) {
			this.on('clusterclick', function (a) {
				if (map.getMaxZoom() === map.getZoom()) {
					if (spiderfyOnMaxZoom) {
						a.layer.spiderfy();
					}
				} else if (zoomToBoundsOnClick) {
					a.layer.zoomToBounds();
				}
			}, this);
		}

		//Show convex hull (boundary) polygon on mouse over
		if (showCoverageOnHover) {
			this.on('clustermouseover', function (a) {
				if (this._inZoomAnimation) {
					return;
				}
				if (shownPolygon) {
					map.removeLayer(shownPolygon);
				}
				if (a.layer.getChildCount() > 2) {
					shownPolygon = new L.Polygon(a.layer.getConvexHull());
					map.addLayer(shownPolygon);
				}
			}, this);
			this.on('clustermouseout', function () {
				if (shownPolygon) {
					map.removeLayer(shownPolygon);
					shownPolygon = null;
				}
			}, this);
			map.on('zoomend', function () {
				if (shownPolygon) {
					map.removeLayer(shownPolygon);
					shownPolygon = null;
				}
			}, this);
		}
	},

	_sqDist: function (p1, p2) {
		var dx = p2.x - p1.x,
			dy = p2.y - p1.y;
		return dx * dx + dy * dy;
	},

	_zoomEnd: function () {

		this._mergeSplitClusters();

		this._zoom = this._map._zoom;
		this._currentShownBounds = this._getExpandedVisibleBounds();
	},

	_moveEnd: function () {
		if (this._inZoomAnimation) {
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

		//console.time('cluster');
		this._topClusterLevel = this._clusterToMarkerCluster(this._needsClustering, maxZoom);
		this._needsClustering = [];

		//Generate to the top
		while (minZoom < this._topClusterLevel._zoom) {
			this._topClusterLevel = this._clusterToMarkerCluster(this._topClusterLevel._childClusters.concat(this._topClusterLevel._markers), this._topClusterLevel._zoom - 1);
		}
		//console.timeEnd('cluster');

		//Remember the current zoom level and bounds
		this._zoom = currentZoom;
		this._currentShownBounds = this._getExpandedVisibleBounds();

		//Make things appear on the map
		this._topClusterLevel._recursivelyAddChildrenToMap(null, currentZoom - minZoom + 1, this._currentShownBounds);
	},

	//Merge and split any existing clusters that are too big or small
	_mergeSplitClusters: function () {

		if (this._zoom < this._map._zoom) { //Zoom in, split
			this._animationStart();
			//Remove clusters now off screen
			this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, this._zoom - this._topClusterLevel._zoom, this._getExpandedVisibleBounds());

			this._animationZoomIn(this._zoom, this._map._zoom);

		} else if (this._zoom > this._map._zoom) { //Zoom out, merge
			this._animationStart();

			this._animationZoomOut(this._zoom, this._map._zoom);
		} else {
			this._moveEnd();
		}
	},

	//Takes a list of markers and clusters the new marker in to them
	//Will return null or the new MarkerCluster. The clustered in marker is removed from the given array
	_clusterOne: function (unclustered, newMarker, markerPoint) {
		var marker = unclustered.getNearObject(markerPoint);

		if (marker) {
			// create a new cluster with these 2
			unclustered.removeObject(marker);
			return new L.MarkerCluster(this, marker, newMarker);
		}

		return null;
	},

	//Takes a list of objects that have a 'getLatLng()' function (Marker / MarkerCluster)
	//Performs clustering on them (using a greedy algorithm) and returns those clusters.
	//markers: List of Markers/MarkerClusters to cluster
	//Returns { 'clusters': [new clusters], 'unclustered': [unclustered markers] }
	_cluster: function (markers, zoom) {
		var radius = this.options.maxClusterRadius,
		    clusters = new L.DistanceGrid(radius),
		    unclustered = new L.DistanceGrid(radius),
		    i, j, marker, markerPoint, cluster, newCluster;

		// go through each point
		for (i = markers.length - 1; i >= 0; i--) {
			marker = markers[i];
			markerPoint = this._map.project(marker.getLatLng(), zoom); // calculate pixel position

			// try add it to an existing cluster
			cluster = clusters.getNearObject(markerPoint);

			if (cluster) {
				cluster._addChild(marker);
			} else {
				// otherwise, look through all of the markers we haven't managed to cluster and see if we should form a cluster with them
				newCluster = this._clusterOne(unclustered, marker, markerPoint);
				if (newCluster) {
					clusters.addObject(newCluster, this._map.project(newCluster.getLatLng(), zoom));
				} else {
					// didn't manage to use it
					unclustered.addObject(marker, markerPoint);
				}
			}
		}

		var result = [],
			group = this;

		// any clusters that did not end up being a child of a new cluster, make them a child of a new cluster
		unclustered.eachObject(function (cluster) {
			if (cluster instanceof L.MarkerCluster) {
				newCluster = new L.MarkerCluster(group, cluster);
				newCluster._haveGeneratedChildClusters = true;

				clusters.addObject(newCluster, cluster._dGridPoint);
				unclustered.removeObject(cluster);

				return true;
			}
			return false;
		});

		unclustered.eachObject(function (marker) {
			result.push(marker);
		});

		// initialize created clusters
		clusters.eachObject(function (cluster) {
			cluster._baseInit();
			result.push(cluster);
		});

		return result;
	},

	//Clusters the given markers (with _cluster) and returns the result as a MarkerCluster
	_clusterToMarkerCluster: function (markers, zoom) {
		var toAdd = this._cluster(markers, zoom),
			result = new L.MarkerCluster(this),
			i;

		for (i = toAdd.length - 1; i >= 0; i--) {
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
			width =  L.Browser.mobile ? 0 : Math.abs(bounds.max.x - bounds.min.x),
			height = L.Browser.mobile ? 0 : Math.abs(bounds.max.y - bounds.min.y),
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
		if (newCluster === true) {
			L.FeatureGroup.prototype.addLayer.call(this, layer);
		} else if (newCluster._childCount === 2) {
			newCluster._addToMap();
			newCluster._recursivelyRemoveChildrenFromMap(newCluster._bounds, this._map.getMaxZoom()); //getMaxZoom will always get all children
		}
	}
} : {

	//Animated versions here
	_animationStart: function () {
		this._map._mapPane.className += ' leaflet-cluster-anim';
		this._inZoomAnimation++;
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

		this._forceLayout();
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

		var me = this;

		//Update the opacity (If we immediately set it they won't animate)
		this._forceLayout();
		marker._recursivelyBecomeVisible(bounds, depthToStartAt);

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
		if (newCluster !== true) {
			if (newCluster._childCount > 2) { //Was already a cluster

				this._forceLayout();
				this._animationStart();

				layer._setPos(this._map.latLngToLayerPoint(newCluster.getLatLng()));
				layer.setOpacity(0);

				setTimeout(function () {
					L.FeatureGroup.prototype.removeLayer.call(me, layer);
					layer.setOpacity(1);

					me._animationEnd();
				}, 250);

			} else { //Just became a cluster
				this._forceLayout();

				me._animationStart();
				me._animationZoomOutSingle(newCluster, 0, this._map.getMaxZoom());
			}
		}
	},

	//Force a browser layout of stuff in the map
	// Should apply the current opacity and location to all elements so we can update them again for an animation
	_forceLayout: function () {
		//In my testing this works, infact offsetWidth of any element seems to work.
		//Could loop all this._layers and do this for each _icon if it stops working

		L.Util.falseFn(document.body.offsetWidth);
	}
});


L.MarkerCluster = L.Marker.extend({
	initialize: function (group, a, b) {
		this._group = group;

		this._markers = [];
		this._childClusters = [];
		this._childCount = 0;

		this._bounds = new L.LatLngBounds();

		if (a) {
			this._addChild(a);
		}
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
		this._latlng = this._wLatLng;
		L.Marker.prototype.initialize.call(this, this._latlng, { icon: this._group.options.iconCreateFunction(this._childCount) });
	},

	_addChild: function (new1) {
		this._expandBounds(new1);
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

	},

	_expandBounds: function (marker) {

		var addedCount,
		    addedLatLng = marker._latlng;

		if (marker instanceof L.MarkerCluster) {
			this._bounds.extend(marker._bounds);
			addedCount = marker._childCount;
		} else {
			this._bounds.extend(addedLatLng);
			addedCount = 1;
		}

		if (!this._latlng) {
			// when clustering, take position of the first point as the cluster center
			this._latlng = this._cLatLng = addedLatLng;
		}

		// when showing clusters, take weighted average of all points as cluster center
		var totalCount = this._childCount + addedCount;

		//Calculate weighted latlng for display
		if (!this._wLatLng) {
			this._wLatLng = new L.LatLng(addedLatLng.lat, addedLatLng.lng);
		} else {
			this._wLatLng.lat = (addedLatLng.lat * addedCount + this._wLatLng.lat * this._childCount) / totalCount;
			this._wLatLng.lng = (addedLatLng.lng * addedCount + this._wLatLng.lng * this._childCount) / totalCount;
		}
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
	//  true: was able to put this marker in, but don't know its current visible parents position (If returned externally, add this marker at its position)
	//  false: wasn't able to put this marker in
	//  a MarkerCluster: the visible parent of the marker
	_recursivelyAddLayer: function (layer, zoom) {
		var map = this._group._map,
			maxClusterRadius = this._group.options.maxClusterRadius,
			result = false,
			i;

		for (i = this._childClusters.length - 1; i >= 0; i--) {
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
		if (!result && (this._canAcceptPosition(layer.getLatLng(), zoom) || ('_zoom' in this))) {

			//Add to ourself instead
			var layerPos = map.project(layer.getLatLng(), zoom + 1),
				sqDist = this._group._sqDist;

			//var distanceGrid = new L.DistanceGrid(maxClusterRadius);
			for (i = this._markers.length - 1; i >= 0; i--) {
				var m = this._markers[i];
				if (sqDist(layerPos, map.project(m.getLatLng(), zoom + 1)) < (maxClusterRadius * maxClusterRadius)) {
					result = m;
					this._markers.splice(i, 1);
					this._childCount--;
					break;
				}
			}

			//result = distanceGrid.getNearObject(map.project(layer.getLatLng(), zoom + 1));

			if (result) {
				//Create a new cluster for them
				result = new L.MarkerCluster(this._group, result, layer);
				result._baseInit();

				//Add our new child
				this._addChild(result);

				//We may be above the zoom that these 2 markers would initially cluster at
				// so push the new cluster as deep as it can go
				var wantedZoom = map.getZoom() - 1,
					maxZoom = map.getMaxZoom(),
					newResult,
					finalResult = (zoom === wantedZoom) ? result : true;
				while (zoom < maxZoom) {
					zoom++;

					//Shouldn't be a cluster at this level
					if (sqDist(map.project(layer.getLatLng(), zoom + 1), map.project(result._markers[0].getLatLng(), zoom + 1)) >= (maxClusterRadius * maxClusterRadius)) {
						break;
					}

					newResult = new L.MarkerCluster(this._group, result._markers[0], layer);
					newResult._baseInit();
					result._markers = [];
					result._childClusters.push(newResult);
					result = newResult;

					if (zoom === wantedZoom) {
						finalResult = result;
					}
				}
				result = finalResult;

			} else {
				this._addChild(layer);
				result = true;
			}
		}

		if (result) {
			if (!('_zoom' in this)) {
				this.setIcon(this._group.options.iconCreateFunction(this._childCount));
			}
			this._recalculateBounds();
		}
		if (result === true) {
			if (this._icon) {
				result = this;
			}
		}

		return result;
	},

	_canAcceptPosition: function (latlng, zoom) {
		if (this._childCount === 0) {
			return true;
		}

		var clusterRadiusSqrd = this._group.options.maxClusterRadius * this._group.options.maxClusterRadius,
			pos = this._group._map.project(this._cLatLng, zoom),
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
				this._childCount--;
				this._recalculateBounds();

				if (!('_zoom' in this)) {
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
				if (!('_zoom' in this)) {
					this.setIcon(group.options.iconCreateFunction(this._childCount));
				}

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

		if (this._childCount === 0) {
			delete this._latlng;
		} else {
			this.setLatLng(this._wLatLng);
		}
	},


	//Returns true if we are the parent of only one cluster and that cluster is the same as us
	_isSingleParent: function () {
		//Don't need to check this._markers as the rest won't work if there are any
		return this._childClusters.length > 0 && this._childClusters[0]._childCount === this._childCount;
	}
});



L.DistanceGrid = function (cellSize) {
	this._cellSize = cellSize;
	this._sqCellSize = cellSize * cellSize;
	this._grid = {};
};

L.DistanceGrid.prototype = {

	addObject: function (obj, point) {
		var x = this._getCoord(point.x),
		    y = this._getCoord(point.y),
		    grid = this._grid,
		    row = grid[y] = grid[y] || {},
		    cell = row[x] = row[x] || [];

		obj._dGridCell = cell;
		obj._dGridPoint = point;

		cell.push(obj);
	},

	updateObject: function (obj, point) {
		this.removeObject(obj);
		this.addObject(obj, point);
	},

	removeObject: function (obj) {
		var oldCell = obj._dGridCell,
		    point = obj._dGridPoint,
		    i, len, x, y;

		for (i = 0, len = oldCell.length; i < len; i++) {
			if (oldCell[i] === obj) {

				oldCell.splice(i, 1);

				if (len === 1) {
					x = this._getCoord(point.x);
					y = this._getCoord(point.y);
					delete this._grid[y][x];
				}

				break;
			}
		}
	},

	eachObject: function (fn, context) {
		var i, j, k, len, row, cell, removed,
		    grid = this._grid;

		for (i in grid) {
			if (grid.hasOwnProperty(i)) {
				row = grid[i];

				for (j in row) {
					if (row.hasOwnProperty(j)) {
						cell = row[j];

						for (k = 0, len = cell.length; k < len; k++) {
							removed = fn.call(context, cell[k]);
							if (removed) {
								k--;
								len--;
							}
						}
					}
				}
			}
		}
	},

	getNearObject: function (point) {
		var x = this._getCoord(point.x),
		    y = this._getCoord(point.y),
		    i, j, k, row, cell, len, obj;

		for (i = y - 1; i <= y + 1; i++) {
			row = this._grid[i];
			if (row) {

				for (j = x - 1; j <= x + 1; j++) {
					cell = row[j];
					if (cell) {

						for (k = 0, len = cell.length; k < len; k++) {
							obj = cell[k];
							if (this._sqDist(obj._dGridPoint, point) < this._sqCellSize) {
								return obj;
							}
						}
					}
				}
			}
		}

		return null;
	},

	_getCoord: function (x) {
		return Math.floor(x / this._cellSize);
	},

	_sqDist: function (p, p2) {
		var dx = p2.x - p.x,
		    dy = p2.y - p.y;
		return dx * dx + dy * dy;
	}
};


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
		if (this._group._spiderfied === this || this._group._inZoomAnimation) {
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

	unspiderfy: function (zoomDetails) {
		/// <param Name="zoomDetails">Argument from zoomanim if being called in a zoom animation or null otherwise</param>
		if (this._group._inZoomAnimation) {
			return;
		}
		this._animationUnspiderfy(zoomDetails);

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
			res[i] = new L.Point(centerPt.x + legLength * Math.cos(angle), centerPt.y + legLength * Math.sin(angle))._round();
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
			res[i] = new L.Point(centerPt.x + legLength * Math.cos(angle), centerPt.y + legLength * Math.sin(angle))._round();
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
			i, m, leg, newPos;

		for (i = childMarkers.length - 1; i >= 0; i--) {
			newPos = map.layerPointToLatLng(positions[i]);
			m = childMarkers[i];

			m._preSpiderfyLatlng = m._latlng;
			m.setLatLng(newPos);
			m.setZIndexOffset(1000000); //Make these appear on top of EVERYTHING

			L.FeatureGroup.prototype.addLayer.call(group, m);


			leg = new L.Polyline([this._latlng, newPos], { weight: 1.5, color: '#222' });
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

			L.FeatureGroup.prototype.removeLayer.call(group, m);

			m.setLatLng(m._preSpiderfyLatlng);
			delete m._preSpiderfyLatlng;
			m.setZIndexOffset(0);

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
			thisLayerPos = map.latLngToLayerPoint(this._latlng),
			i, m, leg, newPos;

		//Add markers to map hidden at our center point
		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			m.setZIndexOffset(1000000); //Make these appear on top of EVERYTHING
			m.setOpacity(0);

			L.FeatureGroup.prototype.addLayer.call(group, m);

			m._setPos(thisLayerPos);
		}

		group._forceLayout();
		group._animationStart();

		var initialLegOpacity = L.Browser.svg ? 0 : 0.3,
			xmlns = L.Path.SVG_NS;


		for (i = childMarkers.length - 1; i >= 0; i--) {
			newPos = map.layerPointToLatLng(positions[i]);
			m = childMarkers[i];

			//Move marker to new position
			m._preSpiderfyLatlng = m._latlng;
			m.setLatLng(newPos);
			m.setOpacity(1);


			//Add Legs.
			leg = new L.Polyline([me._latlng, newPos], { weight: 1.5, color: '#222', opacity: initialLegOpacity });
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
		// If the initial opacity of the spiderlegs isn't 0 then they appear before the animation starts.
		if (L.Browser.svg) {
			this._group._forceLayout();

			for (i = childMarkers.length - 1; i >= 0; i--) {
				m = childMarkers[i]._spiderLeg;

				m.options.opacity = 0.5;
				m._path.setAttribute('stroke-opacity', 0.5);
			}
		}

		setTimeout(function () {
			group._animationEnd();
		}, 250);
	},

	_animationUnspiderfy: function (zoomDetails) {
		var group = this._group,
			map = group._map,
			thisLayerPos = zoomDetails ? map._latLngToNewLayerPoint(this._latlng, zoomDetails.zoom, zoomDetails.center) : map.latLngToLayerPoint(this._latlng),
			childMarkers = this.getAllChildMarkers(),
			svg = L.Browser.svg,
			m, i, a;

		group._animationStart();
		
		//Make us visible and bring the child markers back in
		this.setOpacity(1);
		for (i = childMarkers.length - 1; i >= 0; i--) {
			m = childMarkers[i];

			//Fix up the location to the real one
			m.setLatLng(m._preSpiderfyLatlng);
			delete m._preSpiderfyLatlng;
			//Hack override the location to be our center
			m._setPos(thisLayerPos);

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
			//If we have only <= one child left then that marker will be shown on the map so don't remove it!
			var stillThereChildCount = 0;
			for (i = childMarkers.length - 1; i >= 0; i--) {
				m = childMarkers[i];
				if (m._spiderLeg) {
					stillThereChildCount++;
				}
			}


			for (i = childMarkers.length - 1; i >= 0; i--) {
				m = childMarkers[i];

				if (!m._spiderLeg) { //Has already been unspiderfied
					continue;
				}


				m.setOpacity(1);
				m.setZIndexOffset(0);

				if (stillThereChildCount > 1) {
					L.FeatureGroup.prototype.removeLayer.call(group, m);
				}

				map.removeLayer(m._spiderLeg);
				delete m._spiderLeg;
			}
			group._animationEnd();
		}, 250);
	}
});


L.MarkerClusterGroup.include({
	//The MarkerCluster currently spiderfied (if any)
	_spiderfied: null,

	_spiderfierOnAdd: function () {
		this._map.on('click', this._unspiderfyWrapper, this);
		this._map.on('zoomstart', this._unspiderfyZoomStart, this);

		if (L.Browser.svg && !L.Browser.touch) {
			this._map._initPathRoot();
			//Needs to happen in the pageload, not after, or animations don't work in webkit
			//  http://stackoverflow.com/questions/8455200/svg-animate-with-dynamically-added-elements
			//Disable on touch browsers as the animation messes up on a touch zoom and isn't very noticable
		}
	},

	_spiderfierOnRemove: function () {
		this._map.off('click', this._unspiderfyWrapper, this);
		this._map.off('zoomstart', this._unspiderfyZoomStart, this);
	},


	//On zoom start we add a zoomanim handler so that we are guaranteed to be last (after markers are animated)
	//This means we can define the animation they do rather than Markers doing an animation to their actual location
	_unspiderfyZoomStart: function () {
		this._map.on('zoomanim', this._unspiderfyZoomAnim, this);
	},
	_unspiderfyZoomAnim: function (zoomDetails) {
		//Wait until the first zoomanim after the user has finished touch-zooming before running the animation
		if (L.DomUtil.hasClass(this._map._mapPane, 'leaflet-touching')) {
			return;
		}

		this._map.off('zoomanim', this._unspiderfyZoomAnim, this);
		this._unspiderfy(zoomDetails);
	},


	_unspiderfyWrapper: function () {
		/// <summary>_unspiderfy but passes no arguments</summary>
		this._unspiderfy();
	},

	_unspiderfy: function (zoomDetails) {
		if (this._spiderfied) {
			this._spiderfied.unspiderfy(zoomDetails);
		}
	},

	//If the given layer is currently being spiderfied then we unspiderfy it so it isn't on the map anymore etc
	_unspiderfyLayer: function (layer) {
		if (layer._spiderLeg) {
			L.FeatureGroup.prototype.removeLayer.call(this, layer);

			layer.setOpacity(1);
			//Position will be fixed up immediately in _animationUnspiderfy
			layer.setZIndexOffset(0);

			this._map.removeLayer(layer._spiderLeg);
			delete layer._spiderLeg;
		}
	}
});



}(this));