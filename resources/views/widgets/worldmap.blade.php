<div id="worldmap_widget-{{ $id }}" class="worldmap_widget" data-reload="false"></div>

<script type="application/javascript">
    (function () {
        const map_id = 'worldmap_widget-{{ $id }}';
        const status = {{ Js::from($status) }};
        const device_group = {{ (int) $device_group }};
        const map_config = {{ Js::from($map_config) }};
        const group_radius = {{ (int) $group_radius }};

        loadjs('js/leaflet.js', function () {
            loadjs('js/leaflet.markercluster.js', function () {
                loadjs('js/leaflet.awesome-markers.min.js', function () {
                    loadjs('js/L.Control.Locate.min.js', function () {
                        init_map(map_id, map_config).scrollWheelZoom.disable();
                        populate_map_markers(map_id, group_radius, status, device_group);

                        // register listeners
                        $('#' + map_id).on('click', function (event) {
                            get_map(map_id).scrollWheelZoom.enable();
                        }).on('mouseleave', function (event) {
                            get_map(map_id).scrollWheelZoom.disable();
                        }).on('resize', function (event) {
                            get_map(map_id).invalidateSize();
                        }).on('refresh', function (event) {
                            get_map(map_id).invalidateSize();
                            populate_map_markers(map_id, group_radius, status, device_group);
                        }).on('destroy', function (event) {
                            destroy_map(map_id);
                        });
                    });
                });
            });
        });
    })();
</script>
