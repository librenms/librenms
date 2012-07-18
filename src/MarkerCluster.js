L.MarkerCluster = L.Marker.extend({
	initialize: function (group, a, b) {
		this._group = group;

		this._markers = [];
		this._childClusters = [];
		this._childCount = 0;

		this._haveGeneratedChildClusters = false;

		this._bounds = new L.LatLngBounds();

		this._addChild(a);
		if (b) {
			this._addChild(b);
		}
	},

	//Recursively retrieve all child markers of this cluster
	getAllChildMarkers: function (storageArray) {
		storageArray = storageArray || [];

		for (var i = 0; i < this._childClusters.length; i++) {
			this._childClusters[i].getAllChildMarkers(storageArray);
		}

		for (var j = 0; j < this._markers.length; j++) {
			storageArray.push(this._markers[j]);
		}

		return storageArray;
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

	//Removes the given node from this marker cluster (or its child as required)
	//Returns true if it (or its childCluster) removes the marker
	_recursivelyRemoveChildMarker: function(layer) {
		var markers = this._markers,
			childClusters = this._childClusters,
			i;

		//Check our children
		for (i = markers.length - 1; i >= 0; i--) {
			if (markers[i] == layer) {
				markers.splice(i, 1);
				this._recalculateBounds();
		
				this._childCount--;
				if (this._icon) {
					this.setIcon(this._group.options.iconCreateFunction(this._childCount));
				}
				return true;
			}
		}

		//Otherwise check our childClusters
		for (i = childClusters.length - 1; i >= 0; i--) {
			var child = childClusters[i];
			if (child._recursivelyRemoveChildMarker(layer)) {
				this._childCount--;

				//if our child cluster is no longer a cluster, remove it and replace with just the marker
				if (child._childCount === 1) {
					markers.push(child._markers[0]);
					childClusters.splice(i, 1);
				}

				this._recalculateBounds();

				if (this._icon) {
					this.setIcon(this._group.options.iconCreateFunction(this._childCount));
				}
				return true;
			}
		}

		return false;
	},

	_recursivelyRemoveChildrenAndAddNowVisibleMarkers: function (bounds, depthToStartAt, depthToAnimateIn) {
		//TODO: Care more about bounds?
		var childClusters = this._childClusters,
			markers = this._markers,
			i;

		if (depthToStartAt === 0) {
			this._recursivelyRemoveChildrenFromMap(bounds, depthToAnimateIn); //TODO: previousBounds, not bounds

		} else {
			for (i = childClusters.length - 1; i >= 0; i--) {
				if (bounds.intersects(childClusters[i]._bounds)) {
					childClusters[i]._recursivelyRemoveChildrenAndAddNowVisibleMarkers(bounds, depthToStartAt - 1, depthToAnimateIn);
				}
			}

			if (depthToStartAt == 1) {
				for (i = markers.length - 1; i >= 0; i--) {
					var m = markers[i];
					if (bounds.contains(m._latlng)) {
						L.FeatureGroup.prototype.addLayer.call(this._group, m);
					}
				}
			}
		}
	},

	_recursivelyAnimateChildrenIn: function (center, depth) {
		var markers = this._markers,
		    markersLength = markers.length,
		    childClusters = this._childClusters,
		    childClustersLength = childClusters.length;

		for (var i = 0; i < markersLength; i++) {
			var m = markers[i];

			//Only do it if the icon is still on the map
			if (m._icon) {
				m._setPos(center);
				m.setOpacity(0);
			}
		}

		if (depth === 1) {
			for (var j = 0; j < childClustersLength; j++) {
				var cm = childClusters[j];
				if (cm._icon) {
					cm._setPos(center);
					cm.setOpacity(0);
				}
			}
		} else {
			for (var k = 0; k < childClustersLength; k++) {
				childClusters[k]._recursivelyAnimateChildrenIn(center, depth - 1);
			}
		}
	},

	_recursivelyAnimateChildrenInAndAddSelfToMap: function (bounds, depthToStartAt, depthToAnimateIn) {
		//TODO: Care more about bounds?
		var childClusters = this._childClusters,
			i;

		if (depthToStartAt == 0) {
			this._recursivelyAnimateChildrenIn(this._group._map.latLngToLayerPoint(this.getLatLng()).round(), depthToAnimateIn);

			//Add Self
			if (bounds.contains(this._latlng)) { //Add the new cluster but have it be hidden (so it gets animated, display=none stops transition)

				//TODO: depthToAnimateIn affects _isSingleParent, if there is a multizoom we may/may not be.
				if (this._isSingleParent() && depthToAnimateIn === 1) { //If we are the same as our parent, don't do an animation, just immediately appear
					this.setOpacity(1);
					this._recursivelyRemoveChildrenFromMap(bounds, depthToAnimateIn); //Immediately remove our children as we are replacing them. TODO previousBounds not bounds
				} else {
					this.setOpacity(0);
				}
				this._addToMap();
			}

		} else {
			for (i = childClusters.length - 1; i >= 0; i--) {
				if (bounds.intersects(childClusters[i]._bounds)) {
					childClusters[i]._recursivelyAnimateChildrenInAndAddSelfToMap(bounds, depthToStartAt - 1, depthToAnimateIn);
				}
			}
		}
	},

	_recursivelyBecomeVisible: function (bounds, depth) {
		if (depth == 0) {
			this.setOpacity(1);
		} else {
			for (var i = this._childClusters.length - 1; i >= 0; i--) {
				if (bounds.intersects(this._childClusters[i]._bounds)) {
					this._childClusters[i]._recursivelyBecomeVisible(bounds, depth - 1);
				}
			}
		}
	},

	_recursivelyAddChildrenToMap: function (startPos, depth, bounds) {

		if (depth === 0) {
			this._addToMap(startPos);
			return;
		}

		//Add our child markers at startPos (so they can be animated out)
		for (var i = 0; i < this._markers.length; i++) {
			var nm = this._markers[i];

			if (!bounds.contains(nm._latlng)) {
				continue;
			}

			if (startPos) {
				nm._backupLatlng = nm.getLatLng();

				nm.setLatLng(startPos);
				nm.setOpacity(0);
			}

			L.FeatureGroup.prototype.addLayer.call(this._group, nm);
		}

		//Recurse down to child clusters
		for (var k = 0; k < this._childClusters.length; k++) {
			var cc = this._childClusters[k];
			if (bounds.intersects(cc._bounds)) {
				cc._recursivelyAddChildrenToMap(startPos, depth - 1, bounds);
			}
		}
	},

	_recursivelyRestoreChildPositions: function (depth) {
		//Fix positions of child markers
		for (var i = 0; i < this._markers.length; i++) {
			var nm = this._markers[i];
			if (nm._backupLatlng) {
				nm.setLatLng(nm._backupLatlng);
				delete nm._backupLatlng;
			}
		}

		if (depth === 1) {
			//Reposition child clusters
			for (var j = 0; j < this._childClusters.length; j++) {
				this._childClusters[j]._restorePosition();
			}
		} else {
			for (var k = 0; k < this._childClusters.length; k++) {
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

	//depth 1 means I remove my immediate children from the map
	_recursivelyRemoveChildrenFromMap: function (previousBounds, depth) {
		//TODO: Use previousBounds so we only bother looking at ones that weren't on screen
		var m;
		//markers
		for (var i = 0; i < this._markers.length; i++) {
			m = this._markers[i];
			L.FeatureGroup.prototype.removeLayer.call(this._group, m);
			m.setOpacity(1);
		}

		if (depth === 1) {
			//child clusters
			for (var j = 0; j < this._childClusters.length; j++) {
				m = this._childClusters[j];
				L.FeatureGroup.prototype.removeLayer.call(this._group, m);
				m.setOpacity(1);
			}
		} else {
			var childClusters = this._childClusters,
			    childClustersLength = childClusters.length;

			for (var k = 0; k < childClustersLength; k++) {
				childClusters[k]._recursivelyRemoveChildrenFromMap(previousBounds, depth - 1);
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
		return this._childClusters.length > 0 && this._childClusters[0]._childCount == this._childCount;
	}
});