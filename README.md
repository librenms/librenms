Leaflet.markercluster
=====================

Provides Beautiful Animated Marker Clustering functionality for Leaflet

## Using the plugin
See the included examples for usage.
The [realworld example](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering-realworld.388.html) is a good place to start, it uses all of the defaults of the clusterer.
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
By default the Clusterer enables some nice defaults for you:
zoomToBoundsOnClick: When you mouse over a cluster it shows the bounds of its markers.
showCoverageOnHover: When you click a cluster we zoom to its bounds.
spiderfyOnMaxZoom: When you click a cluster at the bottom zoom level we spiderfy it so you can see all of its markers.

You can disable any of these as you want in the options when you create the MarkerClusterGroup:
```javascript
var markers = new L.MarkerClusterGroup({ spiderfyOnMaxZoom: false, showCoverageOnHover: false, zoomToBoundsOnClick: false });
```

### Customising the Clustered Markers
As an option to MarkerClusterGroup you can provide your own function for creating the Icon for the clustered markers.
The default implementation changes color at bounds of 10 and 100, but more advanced uses may require customising this.
You do not need to include the .Default css if you go this way.

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

### Adding and removing Markers
addLayer and removeLayer are supported and they should work for most uses.

### Handling LOTS of markers
The Clusterer can handle 10000 or even 50000 markers (in chrome). IE9 has some issues with 50000.
[realworld 10000 example](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering-realworld.10000.html)
[realworld 50000 example](http://danzel.github.com/Leaflet.markercluster/example/marker-clustering-realworld.50000.html)
Performance optimizations could be done so these are handled more gracefully (Running the initial clustering over multiple JS calls rather than locking the browser for a long time)

### License

Leaflet.markercluster is free software, and may be redistributed under the MIT-LICENSE.
