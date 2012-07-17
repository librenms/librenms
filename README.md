Leaflet.markercluster
=====================

Provides Marker Clustering functionality for Leaflet

## Using the plugin
See the included examples for usage. Or [check out the most basic example](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering.html) or the [everything example](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering-everything.html).

Create a new MarkerClusterGroup, add your markers to it, then add it to the map

```javascript
var markers = new L.MarkerClusterGroup();
markers.addLayer(new L.Marker(getRandomLatLng(map)));
... Add more layers ...
map.addLayer(markers);
```

For the complete example see example/marker-clustering.html

### Customising the Clustered Marker
As an option to MarkerClusterGroup you can provide your own function for creating the Icon for the clustered markers.
The default implementation changes color at bounds of 10 and 100, but more advanced uses may require customising this.

```javascript
var markers = new L.MarkerClusterGroup({ options: {
	iconCreateFunction: function(childCount) {
		return new L.DivIcon({ html: '<b>' + childCount + '</b>' });
	}
}});
```

### Events
If you register for click, mouseover, etc events on the MarkerClusterGroup you will get callbacks for both individual markers and clusters.
Set your callback up as follows to handle both cases:

```javascript
markers.on('click', function (a) {
	if (a.layer instanceof L.MarkerCluster) {
		console.log('cluster ' + a.layer.getAllChildMarkers().length);
	} else {
		console.log('marker ' + a.layer);
	}
});
```

### Getting the bounds of a cluster
When you recieve an event from a cluster you can query it for the bounds.
See [example/marker-clustering-convexhull.html](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering-convexhull.html) for a working example.
```javascript
markers.on('click', function (a) {
	if (a.layer instanceof L.MarkerCluster) {
		map.addLayer(new L.Polygon(a.layer.getConvexHull()));
	}
});
```

### Zooming to the bounds of a cluster
When you recieve an event from a cluster you can zoom to its bounds in one easy step.
See [marker-clustering-zoomtobounds.html](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering-zoomtobounds.html) for a working example.
```javascript
markers.on('click', function (a) {
	if (a.layer instanceof L.MarkerCluster) {
		a.layer.zoomToBounds();
	}
});
```

### License

Leaflet.markercluster is free software, and may be redistributed under the MIT-LICENSE.
