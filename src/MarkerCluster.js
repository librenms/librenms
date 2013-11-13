L.MarkerCluster = L.Marker.extend({
	initialize: function (group, zoom, a, b) {

		L.Marker.prototype.initialize.call(this, a ? (a._cLatLng || a.getLatLng()) : new L.LatLng(0, 0), { icon: this });


		this._group = group;
		this._zoom = zoom;

		this._markers = [];
		this._childClusters = [];
		this._childCount = 0;
		this._iconNeedsUpdate = true;

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

	//Zoom to the minimum of showing all of the child markers, or the extents of this cluster
	zoomToBounds: function () {
		var childClusters = this._childClusters.slice(),
			map = this._group._map,
			boundsZoom = map.getBoundsZoom(this._bounds),
			zoom = this._zoom + 1,
			i;

		while (childClusters.length > 0 && boundsZoom > zoom) {
			zoom++;
			var newClusters = [];
			for (i = 0; i < childClusters.length; i++) {
				newClusters = newClusters.concat(childClusters[i]._childClusters);
			}
			childClusters = newClusters;
		}

		if (boundsZoom > zoom) {
			this._group._map.setView(this._latlng, zoom);
		} else {
			this._group._map.fitBounds(this._bounds);
		}
	},

	getBounds: function () {
		var bounds = new L.LatLngBounds();
		bounds.extend(this._bounds);
		return bounds;
	},

	_updateIcon: function () {
		this._iconNeedsUpdate = true;
		if (this._icon) {
			this.setIcon(this);
		}
	},

	//Cludge for Icon, we pretend to be an icon for performance
	createIcon: function () {
		if (this._iconNeedsUpdate) {
			this._iconObj = this._group.options.iconCreateFunction(this);
			this._iconNeedsUpdate = false;
		}
		return this._iconObj.createIcon();
	},
	createShadow: function () {
		return this._iconObj.createShadow();
	},


	_addChild: function (new1, isNotificationFromChild) {

		this._iconNeedsUpdate = true;
		this._expandBounds(new1);

		if (new1 instanceof L.MarkerCluster) {
			if (!isNotificationFromChild) {
				this._childClusters.push(new1);
				new1.__parent = this;
			}
			this._childCount += new1._childCount;
		} else {
			if (!isNotificationFromChild) {
				this._markers.push(new1);
			}
			this._childCount++;
		}

		if (this.__parent) {
			this.__parent._addChild(new1, true);
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
		this._group._featureGroup.addLayer(this);
	},

	_recursivelyAnimateChildrenIn: function (bounds, center, maxZoom) {
		this._recursively(bounds, 0, maxZoom - 1,
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
		this._recursively(bounds, -1, zoomLevel,
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
						if (nm.setOpacity) {
							nm.setOpacity(0);
						}
					}

					c._group._featureGroup.addLayer(nm);
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

		if (zoomLevel - 1 === this._zoom) {
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
						c._group._featureGroup.removeLayer(m);
						if (m.setOpacity) {
							m.setOpacity(1);
						}
					}
				}
			},
			function (c) {
				//Remove child clusters at just the bottom level
				for (i = c._childClusters.length - 1; i >= 0; i--) {
					m = c._childClusters[i];
					if (!exceptBounds || !exceptBounds.contains(m._latlng)) {
						c._group._featureGroup.removeLayer(m);
						if (m.setOpacity) {
							m.setOpacity(1);
						}
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
			if (runAtBottomLevel && this._zoom === zoomLevelToStop) {
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
		delete this._wLatLng;

		for (i = markers.length - 1; i >= 0; i--) {
			this._expandBounds(markers[i]);
		}
		for (i = childClusters.length - 1; i >= 0; i--) {
			this._expandBounds(childClusters[i]);
		}
	},


	//Returns true if we are the parent of only one cluster and that cluster is the same as us
	_isSingleParent: function () {
		//Don't need to check this._markers as the rest won't work if there are any
		return this._childClusters.length > 0 && this._childClusters[0]._childCount === this._childCount;
	}
});
