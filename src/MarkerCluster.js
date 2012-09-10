L.MarkerCluster = L.Marker.extend({
	initialize: function (group, zoom, a, b) {

		L.Marker.prototype.initialize.call(this, a ? (a._cLatLng || a.getLatLng()) : new L.LatLng(0, 0), { icon: this });


		this._group = group;
		this._zoom = zoom;

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

	//Cludge for Icon
	createIcon: function () {
		return this._group.options.iconCreateFunction(this).createIcon();
	},
	createShadow: function () {
		return this._group.options.iconCreateFunction(this).createShadow(); //FIXME: Should be saving this
	},

	_addChild: function (new1, isNotificationFromChild) {
		this._expandBounds(new1);
		if (new1 instanceof L.MarkerCluster) {
			if (!isNotificationFromChild) {
				this._childClusters.push(new1);
				new1._parent = this;
			}
			this._childCount += new1._childCount;
		} else {
			if (!isNotificationFromChild) {
				this._markers.push(new1);
			}
			this._childCount++;
		}

		if (this._childCount > 10)
			//debugger;

		if (this._icon) {
			this.setIcon(this._group.options.iconCreateFunction(this));
		}

		if (this._parent) {
			this._parent._addChild(new1, true);
		}
	},

	_removeChildMarker: function (marker) {
		var markers = this._markers,
		    group = this._group, i;

		for (i = markers.length - 1; i >= 0; i--) {
			if (markers[i] === marker) {
				markers.splice(i, 1);
				
				var p = this;
				while (p) {
					p._childCount--;
					p._recalculateBounds();
					p = p._parent;
				}

				//TODO?
				//if (!('_zoom' in this)) {
				//	this.setIcon(group.options.iconCreateFunction(this));
				//}
				return true;
			}
		}

	},

		//Expand our bounds and tell our parent to
	_expandBounds: function (marker) {
		var addedCount,
		    addedLatLng = marker._wLatLng || marker._latlng;

		if (marker instanceof L.MarkerCluster) {
			this._bounds.extend(marker._bounds);
			addedCount = marker._childCount;
		} else {
			this._bounds.extend(addedLatLng);
			addedCount = 1;
		}

		if (!this._cLatLng) {
			// when clustering, take position of the first point as the cluster center
			this._cLatLng = marker._cLatLng || addedLatLng;
		}

		// when showing clusters, take weighted average of all points as cluster center
		var totalCount = this._childCount + addedCount;

		//Calculate weighted latlng for display
		if (!this._wLatLng) {
			this._latlng = this._wLatLng = new L.LatLng(addedLatLng.lat, addedLatLng.lng);
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
		var group = this._group,
			map = group._map,
			maxClusterRadius = group.options.maxClusterRadius,
			result = false,
			sqDist = this._group._sqDist,
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

			//If we are allowed to cluster at our childs level
			if (zoom + 1 !== group.options.disableClusteringAtZoom) {

				//Add to ourself instead
				var layerPos = map.project(layer.getLatLng(), zoom + 1);

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

				if (group.options.disableClusteringAtZoom) {
					maxZoom = group.options.disableClusteringAtZoom - 2;
				}

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
				this.setIcon(this._group.options.iconCreateFunction(this));
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
					this.setIcon(group.options.iconCreateFunction(this));
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
					this.setIcon(group.options.iconCreateFunction(this));
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
					this.setIcon(group.options.iconCreateFunction(this));
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

	_recursivelyAnimateChildrenInAndAddSelfToMap: function (bounds, previousZoomLevel, newZoomLevel) {
		this._recursively(bounds, newZoomLevel, 0,
			function (c) {
				c._recursivelyAnimateChildrenIn(bounds, c._group._map.latLngToLayerPoint(c.getLatLng()).round(), previousZoomLevel);

				//TODO: depthToAnimateIn affects _isSingleParent, if there is a multizoom we may/may not be.
				//As a hack we only do a animation free zoom on a single level zoom, if someone does multiple levels then we always animate
				if (c._isSingleParent() && previousZoomLevel - 1 === newZoomLevel) {
					c.setOpacity(1);
					c._recursivelyRemoveChildrenFromMap(bounds, previousZoomLevel); //Immediately remove our children as we are replacing them. TODO previousBounds not bounds
				} else {
					c.setOpacity(0);
				}

				c._addToMap();
			}
		);
	},

	_recursivelyBecomeVisible: function (bounds, zoomLevel) {
		this._recursively(bounds, 0, zoomLevel, null, function (c) {
			c.setOpacity(1);
		});
	},

	_recursivelyAddChildrenToMap: function (startPos, zoomLevel, bounds) {
		this._recursively(bounds, 0, zoomLevel,
			function (c) {
				if (zoomLevel === c._zoom) {
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

	_recursivelyRestoreChildPositions: function (zoomLevel) {
		//Fix positions of child markers
		for (var i = this._markers.length - 1; i >= 0; i--) {
			var nm = this._markers[i];
			if (nm._backupLatlng) {
				nm.setLatLng(nm._backupLatlng);
				delete nm._backupLatlng;
			}
		}

		if (zoomLevel - 1 == this._zoom) {
			//Reposition child clusters
			for (var j = this._childClusters.length - 1; j >= 0; j--) {
				this._childClusters[j]._restorePosition();
			}
		} else {
			for (var k = this._childClusters.length - 1; k >= 0; k--) {
				this._childClusters[k]._recursivelyRestoreChildPositions(zoomLevel);
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
	_recursivelyRemoveChildrenFromMap: function (previousBounds, zoomLevel, exceptBounds) {
		var m, i;
		this._recursively(previousBounds, -1, zoomLevel - 1,
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
	// zoomLevelToStart: zoom level to start running functions (inclusive)
	// zoomLevelToStop: zoom level to stop running functions (inclusive)
	// runAtEveryLevel: function that takes an L.MarkerCluster as an argument that should be applied on every level
	// runAtBottomLevel: function that takes an L.MarkerCluster as an argument that should be applied at only the bottom level
	_recursively: function (boundsToApplyTo, zoomLevelToStart, zoomLevelToStop, runAtEveryLevel, runAtBottomLevel) {
		var childClusters = this._childClusters,
		    zoom = this._zoom,
			i, c;

		if (zoomLevelToStart > zoom) { //Still going down to required depth, just recurse to child clusters
			for (i = childClusters.length - 1; i >= 0; i--) {
				c = childClusters[i];
				if (boundsToApplyTo.intersects(c._bounds)) {
					c._recursively(boundsToApplyTo, zoomLevelToStart, zoomLevelToStop, runAtEveryLevel, runAtBottomLevel);
				}
			}
		} else { //In required depth

			if (runAtEveryLevel) {
				runAtEveryLevel(this);
			}
			if (runAtBottomLevel && this._zoom == zoomLevelToStop) {
				runAtBottomLevel(this);
			}

			//TODO: This loop is almost the same as above
			if (zoomLevelToStop > zoom) {
				for (i = childClusters.length - 1; i >= 0; i--) {
					c = childClusters[i];
					if (boundsToApplyTo.intersects(c._bounds)) {
						c._recursively(boundsToApplyTo, zoomLevelToStart, zoomLevelToStop, runAtEveryLevel, runAtBottomLevel);
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
