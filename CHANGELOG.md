Leaflet.markercluster
=====================

(all changes without author notice are by [@danzel](https://github.com/danzel))

## Master

### Improvements

 * Work better with custom projections (by [@andersarstrand](https://github.com/andersarstrand)) [#74](https://github.com/Leaflet/Leaflet.markercluster/issues/74)
 * Add custom getBounds that works (Reported by [@2803media](https://github.com/2803media))
 * Allow spacing spiderfied icons further apart (Reported by [@stevevance](https://github.com/stevevance)) [#100](https://github.com/Leaflet/Leaflet.markercluster/issues/100)
 * Add custom eachLayer that works (Reported by [@cilogi](https://github.com/cilogi)) [#102](https://github.com/Leaflet/Leaflet.markercluster/issues/102)

### Bugfixes

 * Fix singleMarkerMode when you aren't on the map (by [@duncanparkes](https://github.com/duncanparkes)) [#77](https://github.com/Leaflet/Leaflet.markercluster/issues/77)
 * Fix clearLayers when you aren't on the map (by [@duncanparkes](https://github.com/duncanparkes)) [#79](https://github.com/Leaflet/Leaflet.markercluster/issues/79)
 * IE10 Bug fix (Reported by [@theLundquist](https://github.com/theLundquist)) [#86](https://github.com/Leaflet/Leaflet.markercluster/issues/86)
 * Fixes for hasLayer after removing a layer (Reported by [@cvisto](https://github.com/cvisto)) [#44](https://github.com/Leaflet/Leaflet.markercluster/issues/44)
 * Fix clearLayers not unsetting __parent of the markers, preventing them from being re-added. (Reported by [@apuntovanini](https://github.com/apuntovanini)) [#99](https://github.com/Leaflet/Leaflet.markercluster/issues/99)
 * Fix map.removeLayer(markerClusterGroup) not working (Reported by [@Driklyn](https://github.com/Driklyn)) [#108](https://github.com/Leaflet/Leaflet.markercluster/issues/108)
 * Fix map.addLayers not updating cluster icons (Reported by [@Driklyn](https://github.com/Driklyn)) [#114](https://github.com/Leaflet/Leaflet.markercluster/issues/114)
 * Fix spiderfied clusters breaking if a marker is added to them (Reported by [@Driklyn](https://github.com/Driklyn)) [#114](https://github.com/Leaflet/Leaflet.markercluster/issues/114)

## 0.2 (2012-10-11)

### Improvements

 * Add addLayers/removeLayers bulk add and remove functions that perform better than the individual methods
 * Allow customising the polygon generated for showing the area a cluster covers (by [@yohanboniface](https://github.com/yohanboniface)) [#68](https://github.com/Leaflet/Leaflet.markercluster/issues/68)
 * Add zoomToShowLayer method to zoom down to a marker then call a callback once it is visible
 * Add animateAddingMarkers to allow disabling animations caused when adding/removing markers
 * Add hasLayer
 * Pass the L.MarkerCluster to iconCreateFunction to give more flexibility deciding the icon
 * Make addLayers support geojson layers
 * Allow disabling clustering at a given zoom level
 * Allow styling markers that are added like they were clusters of size 1


### Bugfixes

 * Support when leaflet is configured to use canvas rather than SVG
 * Fix some potential crashes in zoom handlers
 * Tidy up when we are removed from the map

## 0.1 (2012-08-16)

Initial Release!
