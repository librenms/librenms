Leaflet.markercluster
=====================

Provides Beautiful Animated Marker Clustering functionality for [Leaflet](http://leafletjs.com), a JS library for interactive maps.

*Requires Leaflet 0.7.0 or newer.*

For a Leaflet 0.5 compatible version, [Download b128e950](https://github.com/Leaflet/Leaflet.markercluster/archive/b128e950d8f5d7da5b60bd0aa9a88f6d3dd17c98.zip)<br>
For a Leaflet 0.4 compatible version, [Download the 0.2 release](https://github.com/Leaflet/Leaflet.markercluster/archive/0.2.zip)

## Using the plugin
See the included examples for usage.

The [realworld example](http://leaflet.github.com/Leaflet.markercluster/example/marker-clustering-realworld.388.html) is a good place to start, it uses all of the defaults of the clusterer.
Or check out the [custom example](http://leaflet.github.com/Leaflet.markercluster/example/marker-clustering-custom.html) for how to customise the behaviour and appearance of the clusterer

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
* **showCoverageOnHover**: When you mouse over a cluster it shows the bounds of its markers.
* **zoomToBoundsOnClick**: When you click a cluster we zoom to its bounds.
* **spiderfyOnMaxZoom**: When you click a cluster at the bottom zoom level we spiderfy it so you can see all of its markers.
* **removeOutsideVisibleBounds**: Clusters and markers too far from the viewport are removed from the map for performance.

You can disable any of these as you want in the options when you create the MarkerClusterGroup:
```javascript
var markers = new L.MarkerClusterGroup({ spiderfyOnMaxZoom: false, showCoverageOnHover: false, zoomToBoundsOnClick: false });
```

### Customising the Clustered Markers
As an option to MarkerClusterGroup you can provide your own function for creating the Icon for the clustered markers.
The default implementation changes color at bounds of 10 and 100, but more advanced uses may require customising this.
You do not need to include the .Default css if you go this way.
You are passed a MarkerCluster object, you'll probably want to use getChildCount() or getAllChildMarkers() to work out the icon to show

```javascript
var markers = new L.MarkerClusterGroup({
	iconCreateFunction: function(cluster) {
		return new L.DivIcon({ html: '<b>' + cluster.getChildCount() + '</b>' });
	}
});
```
Check out the [custom example](http://leaflet.github.com/Leaflet.markercluster/example/marker-clustering-custom.html) for an example of this.

### All Options
Enabled by default (boolean options):
* **showCoverageOnHover**: When you mouse over a cluster it shows the bounds of its markers.
* **zoomToBoundsOnClick**: When you click a cluster we zoom to its bounds.
* **spiderfyOnMaxZoom**: When you click a cluster at the bottom zoom level we spiderfy it so you can see all of its markers.
* **removeOutsideVisibleBounds**: Clusters and markers too far from the viewport are removed from the map for performance.

Other options
* **animateAddingMarkers**: If set to true then adding individual markers to the MarkerClusterGroup after it has been added to the map will add the marker and animate it in to the cluster. Defaults to false as this gives better performance when bulk adding markers. addLayers does not support this, only addLayer with individual Markers.
* **disableClusteringAtZoom**: If set, at this zoom level and below markers will not be clustered. This defaults to disabled. [See Example](http://leaflet.github.com/Leaflet.markercluster/example/marker-clustering-realworld-maxzoom.388.html)
* **maxClusterRadius**: The maximum radius that a cluster will cover from the central marker (in pixels). Default 80. Decreasing will make more smaller clusters.
* **polygonOptions**: Options to pass when creating the L.Polygon(points, options) to show the bounds of a cluster
* **singleMarkerMode**: If set to true, overrides the icon for all added markers to make them appear as a 1 size cluster
* **spiderfyDistanceMultiplier**: Increase from 1 to increase the distance away from the center that spiderfied markers are placed. Use if you are using big marker icons (Default:1)
* **iconCreateFunction**: Function used to create the cluster icon [See default as example](https://github.com/Leaflet/Leaflet.markercluster/blob/15ed12654acdc54a4521789c498e4603fe4bf781/src/MarkerClusterGroup.js#L542).

## Events
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

## Methods

### Getting the bounds of a cluster
When you recieve an event from a cluster you can query it for the bounds.
See [example/marker-clustering-convexhull.html](http://leaflet.github.com/Leaflet.markercluster/example/marker-clustering-convexhull.html) for a working example.
```javascript
markers.on('clusterclick', function (a) {
	map.addLayer(new L.Polygon(a.layer.getConvexHull()));
});
```

### Zooming to the bounds of a cluster
When you recieve an event from a cluster you can zoom to its bounds in one easy step.
If all of the markers will appear at a higher zoom level, that zoom level is zoomed to instead.
See [marker-clustering-zoomtobounds.html](http://leaflet.github.com/Leaflet.markercluster/example/marker-clustering-zoomtobounds.html) for a working example.
```javascript
markers.on('clusterclick', function (a) {
	a.layer.zoomToBounds();
});
```

### Getting the visible parent of a marker
If you have a marker in your MarkerClusterGroup and you want to get the visible parent of it (Either itself or a cluster it is contained in that is currently visible on the map).
This will return null if the marker and its parent clusters are not visible currently (they are not near the visible viewpoint)
```
var visibleOne = markerClusterGroup.getVisibleParent(myMarker);
console.log(visibleOne.getLatLng());
```

### Adding and removing Markers
addLayer, removeLayer and clearLayers are supported and they should work for most uses.

### Bulk adding and removing Markers
addLayers and removeLayers are bulk methods for adding and removing markers and should be favoured over the single versions when doing bulk addition/removal of markers. Each takes an array of markers

If you are removing a lot of markers it will almost definitely be better to call clearLayers then call addLayers to add the markers you don't want to remove back in. See [#59](https://github.com/Leaflet/Leaflet.markercluster/issues/59#issuecomment-9320628) for details.

### Other Methods
````
hasLayer(layer): Returns true if the given layer (marker) is in the MarkerClusterGroup
zoomToShowLayer(layer, callback): Zooms to show the given marker (spidifying if required), calls the callback when the marker is visible on the map
addLayers(layerArray): Adds the markers in the given array from the MarkerClusterGroup in an efficent bulk method.
removeLayers(layerArray): Removes the markers in the given array from the MarkerClusterGroup in an efficent bulk method.
````

## Handling LOTS of markers
The Clusterer can handle 10000 or even 50000 markers (in chrome). IE9 has some issues with 50000.
[realworld 10000 example](http://leaflet.github.com/Leaflet.markercluster/example/marker-clustering-realworld.10000.html)
[realworld 50000 example](http://leaflet.github.com/Leaflet.markercluster/example/marker-clustering-realworld.50000.html)
Performance optimizations could be done so these are handled more gracefully (Running the initial clustering over multiple JS calls rather than locking the browser for a long time)

### License

Leaflet.markercluster is free software, and may be redistributed under the MIT-LICENSE.

[![Build Status](https://travis-ci.org/Leaflet/Leaflet.markercluster.png?branch=master)](https://travis-ci.org/Leaflet/Leaflet.markercluster)