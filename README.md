Leaflet.markercluster
=====================

Provides Beautiful Animated Marker Clustering functionality for Leaflet

## Using the plugin
See the included examples for usage.
The [everything example](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering-everything.html) is a good place to start, it utilises the MarkerCluser.Default class to provide all of the default functionality.
Or check out the [custom example](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering-custom.html) for how to customise the behaviour and appearance of the clusterer

### Usage
Create a new MarkerClusterGroup, add your markers to it, then add it to the map

```javascript
var markers = new L.MarkerClusterGroup();
markers.addLayer(new L.Marker(getRandomLatLng(map)));
... Add more layers ...
map.addLayer(markers);
```

### Defaults
As a safe default you can use the included MarkerCluster.Default.(css/js) to provide default behaviour and appearance of the clusters.

Include the .Default files (or use the prebuilt version) and create a MarkerClusterGroup as follows:
```javascript
var markers = new L.MarkerClusterGroup();
L.MarkerClusterDefault.bindEvents(map, markers);
... Add markers to the MarkerClusterGroup ...
map.addLayer(markers);
```

### Customising the Clustered Markers
As an option to MarkerClusterGroup you can provide your own function for creating the Icon for the clustered markers.
The default implementation changes color at bounds of 10 and 100, but more advanced uses may require customising this.
You do not need to include the .Default files if you go this way.

```javascript
var markers = new L.MarkerClusterGroup({ options: {
	iconCreateFunction: function(childCount) {
		return new L.DivIcon({ html: '<b>' + childCount + '</b>' });
	}
}});
```
Check out the [custom example](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering-custom.html) for an example of this.

### Events
If you register for click, mouseover, etc events just related to Markers in the cluster.
To recieve events for clusters listen to 'cluster' + 'eventIWant', ex: 'clusterclick', 'clustermouseover'.

Set your callback up as follows to handle both cases:

```javascript
markers.on('click', function (a) {
	console.log('marker ' + a.layer);
});

markers.on('clusterclick', function (a) {
	console.log('cluster ' + a.layer.getAllChildMarkers().length);
});
```

### Getting the bounds of a cluster
When you recieve an event from a cluster you can query it for the bounds.
See [example/marker-clustering-convexhull.html](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering-convexhull.html) for a working example.
```javascript
markers.on('clusterclick', function (a) {
	map.addLayer(new L.Polygon(a.layer.getConvexHull()));
});
```

### Zooming to the bounds of a cluster
When you recieve an event from a cluster you can zoom to its bounds in one easy step.
See [marker-clustering-zoomtobounds.html](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering-zoomtobounds.html) for a working example.
```javascript
markers.on('clusterclick', function (a) {
	a.layer.zoomToBounds();
});
```

### License

Leaflet.markercluster is free software, and may be redistributed under the MIT-LICENSE.
