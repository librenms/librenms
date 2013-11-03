Leaflet.markercluster
=====================

(all changes without author notice are by [@danzel](https://github.com/danzel))

## Master

### Improvements

 * Work better with custom projections (by [@andersarstrand](https://github.com/andersarstrand)) [#74](https://github.com/Leaflet/Leaflet.markercluster/issues/74)
 * Add custom getBounds that works (Reported by [@2803media](https://github.com/2803media))
 * Allow spacing spiderfied icons further apart (Reported by [@stevevance](https://github.com/stevevance)) [#100](https://github.com/Leaflet/Leaflet.markercluster/issues/100)
 * Add custom eachLayer that works (Reported by [@cilogi](https://github.com/cilogi)) [#102](https://github.com/Leaflet/Leaflet.markercluster/issues/102)
 * Add an option (removeOutsideVisibleBounds) to prevent removing clusters that are outside of the visible bounds (by [@wildhoney](https://github.com/wildhoney)) [#103](https://github.com/Leaflet/Leaflet.markercluster/issues/103)
 * Add getBounds method to cluster (Reported by [@nderambure](https://github.com/nderambure)) [#88](https://github.com/Leaflet/Leaflet.markercluster/issues/88)
 * Lots of unit tests
 * Support having Circle / CircleMarker as child markers
 * Add factory methods (Reported by [@mourner](https://github.com/mourner)) [#21](https://github.com/Leaflet/Leaflet.markercluster/issues/21)
 * Add getVisibleParent method to allow getting the visible parent cluster or the marker if it is visible. (By [@littleiffel](https://github.com/littleiffel)) [#102](https://github.com/Leaflet/Leaflet.markercluster/issues/102)
 * Allow adding non-clusterable things to a MarkerClusterGroup, we don't cluster them. (Reported by [@benbalter](https://github.com/benbalter)) [#195](https://github.com/Leaflet/Leaflet.markercluster/issues/195)
 * removeLayer supports taking a FeatureGroup (Reported by [@pabloalcaraz](https://github.com/pabloalcaraz)) [#236](https://github.com/Leaflet/Leaflet.markercluster/issues/236)
 * DistanceGrid tests, QuickHull tests and improvements (By [@tmcw](https://github.com/tmcw)) [#247](https://github.com/Leaflet/Leaflet.markercluster/issues/247) [#248](https://github.com/Leaflet/Leaflet.markercluster/issues/248) [#249](https://github.com/Leaflet/Leaflet.markercluster/issues/249)
### Bugfixes

 * Fix singleMarkerMode when you aren't on the map (by [@duncanparkes](https://github.com/duncanparkes)) [#77](https://github.com/Leaflet/Leaflet.markercluster/issues/77)
 * Fix clearLayers when you aren't on the map (by [@duncanparkes](https://github.com/duncanparkes)) [#79](https://github.com/Leaflet/Leaflet.markercluster/issues/79)
 * IE10 Bug fix (Reported by [@theLundquist](https://github.com/theLundquist)) [#86](https://github.com/Leaflet/Leaflet.markercluster/issues/86)
 * Fixes for hasLayer after removing a layer (Reported by [@cvisto](https://github.com/cvisto)) [#44](https://github.com/Leaflet/Leaflet.markercluster/issues/44)
 * Fix clearLayers not unsetting __parent of the markers, preventing them from being re-added. (Reported by [@apuntovanini](https://github.com/apuntovanini)) [#99](https://github.com/Leaflet/Leaflet.markercluster/issues/99)
 * Fix map.removeLayer(markerClusterGroup) not working (Reported by [@Driklyn](https://github.com/Driklyn)) [#108](https://github.com/Leaflet/Leaflet.markercluster/issues/108)
 * Fix map.addLayers not updating cluster icons (Reported by [@Driklyn](https://github.com/Driklyn)) [#114](https://github.com/Leaflet/Leaflet.markercluster/issues/114)
 * Fix spiderfied clusters breaking if a marker is added to them (Reported by [@Driklyn](https://github.com/Driklyn)) [#114](https://github.com/Leaflet/Leaflet.markercluster/issues/114)
 * Don't show coverage for spiderfied clusters as it will be wrong. (Reported by [@ajbeaven](https://github.com/ajbeaven)) [#95](https://github.com/Leaflet/Leaflet.markercluster/issues/95)
 * Improve zoom in/out immediately making all everything disappear, still issues in Firefox [#140](https://github.com/Leaflet/Leaflet.markercluster/issues/140)
 * Fix animation not stopping with only one marker. (Reported by [@Driklyn](https://github.com/Driklyn)) [#146](https://github.com/Leaflet/Leaflet.markercluster/issues/146)
 * Various fixes for new leaflet (Reported by [@PeterAronZentai](https://github.com/PeterAronZentai)) [#159](https://github.com/Leaflet/Leaflet.markercluster/issues/159)
 * Fix clearLayers when we are spiderfying (Reported by [@skullbooks](https://github.com/skullbooks)) [#162](https://github.com/Leaflet/Leaflet.markercluster/issues/162)
 * Fix removing layers in certain situations (Reported by [@bpavot](https://github.com/bpavot)) [#160](https://github.com/Leaflet/Leaflet.markercluster/issues/160)
 * Support calling hasLayer with null (by [@l0c0luke](https://github.com/l0c0luke)) [#170](https://github.com/Leaflet/Leaflet.markercluster/issues/170)
 * Lots of fixes for removing a MarkerClusterGroup from the map (Reported by [@annetdeboer](https://github.com/annetdeboer)) [#200](https://github.com/Leaflet/Leaflet.markercluster/issues/200)
 * Throw error when being added to a map with no maxZoom.
 * Fixes for markers not appearing after a big zoom (Reported by [@arnoldbird](https://github.com/annetdeboer)) [#216](https://github.com/Leaflet/Leaflet.markercluster/issues/216) (Reported by [@mathilde-pellerin](https://github.com/mathilde-pellerin)) [#260](https://github.com/Leaflet/Leaflet.markercluster/issues/260)
 * Fix coverage polygon not being removed when a MarkerClusterGroup is removed  (Reported by [@ZeusTheTrueGod](https://github.com/ZeusTheTrueGod)) [#245](https://github.com/Leaflet/Leaflet.markercluster/issues/245)

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
