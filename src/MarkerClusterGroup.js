
/*
 * L.MarkerClusterGroup extends L.FeatureGroup by clustering the markers contained within
 */

L.MarkerClusterGroup = L.FeatureGroup.extend({

	options: {
		maxClusterRadius: 80, //A cluster will cover at most this many pixels from its center
		iconCreateFunction: null,

		spiderfyOnMaxZoom: true,
		showCoverageOnHover: true,
		zoomToBoundsOnClick: true,
		singleMarkerMode: false,

		disableClusteringAtZoom: null,

		skipDuplicateAddTesting: false,

		//Whether to animate adding markers after adding the MarkerClusterGroup to the map
		// If you are adding individual markers set to true, if adding bulk markers leave false for massive performance gains.
		animateAddingMarkers: false
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

		this._topClusterLevel = new L.MarkerCluster(this, -1);
	},

	addLayer: function (layer) {

		if (layer instanceof L.LayerGroup) {
			for (var i in layer._layers) {
				if (layer._layers.hasOwnProperty(i)) {
					this.addLayer(layer._layers[i]);
				}
			}
			return this;
		}

		if (this.options.singleMarkerMode) {
			layer.options.icon = this.options.iconCreateFunction({
				getChildCount: function () {
					return 1;
				},
				getAllChildMarkers: function () {
					return [layer];
				}
			});
		}

		if (!this._map) {
			this._needsClustering.push(layer);
			return this;
		}

		if (!this.options.skipDuplicateAddTesting && this.hasLayer(layer)) {
			return this;
		}

		//If we have already clustered we'll need to add this one to a cluster

		if (this._unspiderfy) {
			this._unspiderfy();
		}

		this._addLayer(layer, this._maxZoom);

		//Work out what is visible
		var visibleLayer = layer,
			currentZoom = this._map.getZoom();
		if (layer.__cluster) {
			while ((visibleLayer.__cluster || visibleLayer._parent)._zoom >= currentZoom) {
				visibleLayer = (visibleLayer.__cluster || visibleLayer._parent); //TODO Make __cluster the same name as _parent to make this simpler
			}
		}

		//TODO: Find the highest visible blah

		if (this.options.animateAddingMarkers) {
			this._animationAddLayer(layer, visibleLayer);
		} else {
			this._animationAddLayerNonAnimated(layer, visibleLayer);
		}
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

		if (this._unspiderfy) {
			this._unspiderfy();
		}

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

		//Clear the DistanceGrids
		this._gridClusters = { };
		this._gridUnclustered = { };

		//Reset _topClusterLevel
		this._generateInitialClusters();

		return this;
	},

	hasLayer: function (layer) {
		var res = false;

		this._topClusterLevel._recursively(new L.LatLngBounds([layer.getLatLng()]), 0, this._map.getMaxZoom() + 1,
			function (cluster) {
				for (var i = cluster._markers.length - 1; i >= 0 && !res; i--) {
					if (cluster._markers[i] === layer) {
						res = true;
					}
				}
			}, null);
		return res;
	},

	//Overrides FeatureGroup.onAdd
	onAdd: function (map) {
		L.FeatureGroup.prototype.onAdd.call(this, map);

		if (!this._gridClusters) {
			this._generateInitialClusters();
		}

		for (var i = this._needsClustering.length - 1; i >= 0; i--) {
			this._addLayer(this._needsClustering[i], this._maxZoom);
		}
		this._needsClustering = [];

		this._map.on('zoomend', this._zoomEnd, this);
		this._map.on('moveend', this._moveEnd, this);

		if (this._spiderfierOnAdd) { //TODO FIXME: Not sure how to have spiderfier add something on here nicely
			this._spiderfierOnAdd();
		}

		this._bindEvents();


		//Actually add our markers to the map:

		//Remember the current zoom level and bounds
		this._zoom = this._map.getZoom();
		this._currentShownBounds = this._getExpandedVisibleBounds();

		//Make things appear on the map
		this._topClusterLevel._recursivelyAddChildrenToMap(null, this._zoom, this._currentShownBounds);
	},

	//Overrides FeatureGroup.onRemove
	onRemove: function (map) {
		this._map.off('zoomend', this._zoomEnd, this);
		this._map.off('moveend', this._moveEnd, this);

		//In case we are in a cluster animation
		this._map._mapPane.className = this._map._mapPane.className.replace(' leaflet-cluster-anim', '');

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
	_defaultIconCreateFunction: function (cluster) {
		var childCount = cluster.getChildCount();

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
			map.on('layerremove', function (opt) {
				if (shownPolygon && opt.layer === this) {
					map.removeLayer(shownPolygon);
					shownPolygon = null;
				}
			}, this);
		}
	},

	_zoomEnd: function () {
		if (!this._map) { //May have been removed from the map by a zoomEnd handler
			return;
		}
		this._mergeSplitClusters();

		this._zoom = this._map._zoom;
		this._currentShownBounds = this._getExpandedVisibleBounds();
	},

	_moveEnd: function () {
		if (this._inZoomAnimation) {
			return;
		}

		var newBounds = this._getExpandedVisibleBounds();

		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, this._zoom, newBounds);
		this._topClusterLevel._recursivelyAddChildrenToMap(null, this._zoom, newBounds);

		this._currentShownBounds = newBounds;
		return;
	},

	_generateInitialClusters: function () {
		var maxZoom = this._map.getMaxZoom(),
			radius = this.options.maxClusterRadius;

		if (this.options.disableClusteringAtZoom) {
			maxZoom = this.options.disableClusteringAtZoom - 1;
		}
		this._maxZoom = maxZoom;
		this._gridClusters = {};
		this._gridUnclustered = {};

		//Set up DistanceGrids for each zoom
		for (var zoom = maxZoom; zoom >= 0; zoom--) {
			this._gridClusters[zoom] = new L.DistanceGrid(radius);
			this._gridUnclustered[zoom] = new L.DistanceGrid(radius);
		}
	},

	//Zoom: Zoom to start adding at (Pass this._maxZoom to start at the bottom)
	_addLayer: function (layer, zoom) {
		var gridClusters = this._gridClusters,
		    gridUnclustered = this._gridUnclustered, markerPoint;

		//Find the lowest zoom level to slot this one in
		for (; zoom >= 0; zoom--) {
			markerPoint = this._map.project(layer.getLatLng(), zoom); // calculate pixel position

			//Try find a cluster close by
			var closest = gridClusters[zoom].getNearObject(markerPoint);
			if (closest) {
				closest._addChild(layer);
				layer.__cluster = closest;
				return;
			}

			//Try find a marker close by to form a new cluster with
			closest = gridUnclustered[zoom].getNearObject(markerPoint);
			if (closest) {
				if (closest.__cluster) {
					closest.__cluster._removeChildMarker(closest);
				}
				var parent = closest.__cluster || this._topClusterLevel;

				//Create new cluster with these 2 in it
				console.log('creating new cluster with 2 markers at zoom ' + zoom);

				var newCluster = new L.MarkerCluster(this, zoom, closest, layer);
				gridClusters[zoom].addObject(newCluster, this._map.project(closest.getLatLng(), zoom));
				closest.__cluster = newCluster;
				layer.__cluster = newCluster;

				//First create any new intermediate parent clusters that don't exist
				var lastParent = newCluster;
				for (var z = zoom - 1; z > parent._zoom; z--) {
					lastParent = new L.MarkerCluster(this, z, lastParent);
					gridClusters[z].addObject(lastParent, this._map.project(closest.getLatLng(), z));
				}
				parent._addChild(lastParent);

				//Remove closest from this zoom level and any above that it is in, replace with newCluster
				for (var z = zoom; z >= 0; z--) {
					if (!gridUnclustered[z].removeObject(closest, this._map.project(closest.getLatLng(), z))) {
						break;
					}
				}

				return;
			}
			
			//Didn't manage to cluster in at this zoom, record us as a marker here and continue upwards
			gridUnclustered[zoom].addObject(layer, markerPoint);
		}

		return;
	},

	//Merge and split any existing clusters that are too big or small
	_mergeSplitClusters: function () {
		console.log('mergesplit ' + this._zoom + ' -> ' + map._zoom);
		if (this._zoom < this._map._zoom) { //Zoom in, split
			this._animationStart();
			//Remove clusters now off screen
			this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, this._zoom, this._getExpandedVisibleBounds());

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
		    clusters = this._gridClusters[zoom], 
		    unclustered = this._gridUnclustered[zoom],
		    i, j, marker, markerPoint, cluster, newCluster;

		if (!clusters) {
			clusters = new L.DistanceGrid(radius);
			unclustered = new L.DistanceGrid(radius);
			this._gridClusters[zoom] = clusters;
			this._gridUnclustered[zoom] = unclustered;
		}

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
	},

	//Shared animation code
	_animationAddLayerNonAnimated: function (layer, newCluster) {
		if (newCluster === layer) {
			L.FeatureGroup.prototype.addLayer.call(this, layer);
		} else if (newCluster._childCount === 2) {
			newCluster._addToMap();

			var markers = newCluster.getAllChildMarkers();
			L.FeatureGroup.prototype.removeLayer.call(this, markers[0]);
			L.FeatureGroup.prototype.removeLayer.call(this, markers[1]);
		} else {
			newCluster._updateIcon();
		}
		//TODO else update icon?
	}
});

L.MarkerClusterGroup.include(!L.DomUtil.TRANSITION ? {

	//Non Animated versions of everything
	_animationStart: function () {
		//Do nothing...
	},
	_animationZoomIn: function (previousZoomLevel, newZoomLevel) {
		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, previousZoomLevel);
		this._topClusterLevel._recursivelyAddChildrenToMap(null, newZoomLevel, this._getExpandedVisibleBounds());
	},
	_animationZoomOut: function (previousZoomLevel, newZoomLevel) {
		this._topClusterLevel._recursivelyRemoveChildrenFromMap(this._currentShownBounds, previousZoomLevel);
		this._topClusterLevel._recursivelyAddChildrenToMap(null, newZoomLevel, this._getExpandedVisibleBounds());
	},
	_animationAddLayer: function (layer, newCluster) {
		this._animationAddLayerNonAnimated(layer, newCluster);
	}
} : {

	//Animated versions here
	_animationStart: function () {
		this._map._mapPane.className += ' leaflet-cluster-anim';
		this._inZoomAnimation++;
	},
	_animationEnd: function () {
		if (this._map) {
			this._map._mapPane.className = this._map._mapPane.className.replace(' leaflet-cluster-anim', '');
		}
		this._inZoomAnimation--;
	},
	_animationZoomIn: function (previousZoomLevel, newZoomLevel) {
		var me = this,
		    bounds = this._getExpandedVisibleBounds(),
		    i;

		//Add all children of current clusters to map and remove those clusters from map
		this._topClusterLevel._recursively(bounds, previousZoomLevel, 0, function (c) {
			var startPos = c._latlng,
				markers = c._markers,
				m;

			if (c._isSingleParent() && previousZoomLevel + 1 === newZoomLevel) { //Immediately add the new child and remove us
				L.FeatureGroup.prototype.removeLayer.call(me, c);
				c._recursivelyAddChildrenToMap(null, newZoomLevel, bounds);
			} else {
				//Fade out old cluster
				c.setOpacity(0);
				c._recursivelyAddChildrenToMap(startPos, newZoomLevel, bounds);
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
		me._topClusterLevel._recursivelyBecomeVisible(bounds, newZoomLevel);
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
		me._topClusterLevel._recursively(bounds, previousZoomLevel, newZoomLevel, function (c) {
			c._recursivelyRestoreChildPositions(newZoomLevel);
		});

		//Remove the old clusters and close the zoom animation

		setTimeout(function () {
			//update the positions of the just added clusters/markers
			me._topClusterLevel._recursively(bounds, previousZoomLevel, 0, function (c) {
				L.FeatureGroup.prototype.removeLayer.call(me, c);
			});

			me._animationEnd();
		}, 250);
	},

	_animationZoomOut: function (previousZoomLevel, newZoomLevel) {
		this._animationZoomOutSingle(this._topClusterLevel, previousZoomLevel, newZoomLevel);

		//Need to add markers for those that weren't on the map before but are now
		this._topClusterLevel._recursivelyAddChildrenToMap(null, newZoomLevel, this._getExpandedVisibleBounds());
	},
	_animationZoomOutSingle: function (marker, previousZoomLevel, newZoomLevel) {
		var bounds = this._getExpandedVisibleBounds();

		//Animate all of the markers in the clusters to move to their cluster center point
		marker._recursivelyAnimateChildrenInAndAddSelfToMap(bounds, previousZoomLevel, newZoomLevel);

		var me = this;

		//Update the opacity (If we immediately set it they won't animate)
		this._forceLayout();
		marker._recursivelyBecomeVisible(bounds, newZoomLevel);

		//TODO: Maybe use the transition timing stuff to make this more reliable
		//When the animations are done, tidy up
		setTimeout(function () {

			marker._recursively(bounds, newZoomLevel, 0, function (c) {
				c._recursivelyRemoveChildrenFromMap(bounds, previousZoomLevel);
			});
			me._animationEnd();
		}, 250);
	},
	_animationAddLayer: function (layer, newCluster) {
		var me = this;

		L.FeatureGroup.prototype.addLayer.call(this, layer);
		if (newCluster !== layer) {
			if (newCluster._childCount > 2) { //Was already a cluster

				newCluster._updateIcon();
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
				me._animationZoomOutSingle(newCluster, this._map.getMaxZoom(), this._map.getZoom());
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
