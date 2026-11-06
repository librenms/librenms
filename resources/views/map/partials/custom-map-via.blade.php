{{-- Shared custom map link helpers: per-link arrow style, line style and VIA waypoints. Used by the viewer (custom-js) and the editor --}}
@once
<script type="text/javascript">
    var customMapVia = {
        // Build the vis.js arrows config for an edge, honouring the per-edge arrow style (ARROWSTYLE)
        getArrows: function (reverse_arrows, arrow_type, arrow_scale) {
            var type = arrow_type || 'arrow';
            var scale = customMapVia.arrowScale(arrow_scale);
            var head = type === 'none'
                ? {enabled: false}
                : {enabled: true, scaleFactor: scale, type: type};
            if (Boolean(reverse_arrows)) {
                return {from: head, to: {enabled: false}};
            }
            return {to: head, from: {enabled: false}};
        },

        // Arrow size limited to the range the map save accepts (0.1 - 10), 0.6 when unset or invalid
        arrowScale: function (value) {
            var scale = parseFloat(value);
            return isNaN(scale) || scale <= 0 ? 0.6 : Math.min(10, Math.max(0.1, scale));
        },

        // Most waypoints per half of a link, matches the map save validation
        maxWaypoints: 25,

        // "angled" (Weathermap VIASTYLE) renders straight segments; disable smoothing but keep the type
        smoothForStyle: function (style) {
            return style === "angled" ? {enabled: false, type: "angled"} : {enabled: true, type: style};
        },

        // VIA waypoints use the ids <edgeid>_wf_<n> / <edgeid>_wt_<n> for the dot nodes of the from/to half
        waypointId: function (edgeid, fromto, index) {
            return edgeid + "_w" + fromto[0] + "_" + index;
        },

        // Parse a waypoint dot node id, returns {edgeid, half, index} or null if it is not a waypoint
        parseWaypointId: function (id) {
            var m = (typeof id === 'string') ? id.match(/^(.+)_w([ft])_(\d+)$/) : null;
            return m ? {edgeid: m[1], half: m[2] === 'f' ? 'from' : 'to', index: parseInt(m[3])} : null;
        },

        // Pass-through segments use the ids <edgeid>_from_seg_<n> / <edgeid>_to_seg_<n>
        isWaypointSegmentId: function (id) {
            return typeof id === 'string' && /_(from|to)_seg_\d+$/.test(id);
        },

        // Index of the sub-edge (0 = canonical node->first hop, then each segment) that straddles the
        // midpoint of a half-polyline by length. Used to centre the half's label like Weathermap.
        halfMidSubedge: function (points) {
            var total = 0;
            var lens = [];
            for (var i = 0; i + 1 < points.length; i++) {
                var len = Math.hypot(points[i + 1].x - points[i].x, points[i + 1].y - points[i].y);
                lens.push(len);
                total += len;
            }
            var acc = 0;
            for (var j = 0; j < lens.length; j++) {
                acc += lens[j];
                if (acc >= total / 2) {
                    return j;
                }
            }
            return lens.length - 1;
        },

        // VIA waypoints: route one half of an edge through its waypoints. baseEdge is the canonical
        // _from/_to edge and is modified in place: it is rerouted to the first waypoint, an arrowhead
        // pointing at _mid is moved onto the last segment (so it still lands on _mid), and the label is
        // moved to the sub-edge in the middle of the whole half-link. A label stored in via_label (editor)
        // takes precedence over baseEdge.label, as the latter may have been cleared by a previous call.
        // fromPos/midPos are {x, y} of the half's map node and of _mid; label centring is skipped without them.
        // Returns the waypoint dot nodes and pass-through segments to add to the network.
        getEdgeExtras: function (edgeid, baseEdge, fromto, wps, fromPos, midPos, dotSize) {
            var midId = edgeid + "_mid";
            var label = (baseEdge.via_label !== undefined && baseEdge.via_label !== null) ? baseEdge.via_label : baseEdge.label;
            baseEdge.label = label;
            wps = wps || [];
            if (wps.length === 0) {
                baseEdge.to = midId;
                return {nodes: [], segments: []};
            }

            var noArrow = {enabled: false};
            var arrows = baseEdge.arrows || {};
            var toHead = (arrows.to && arrows.to.enabled) ? arrows.to : null;
            if (toHead) {
                baseEdge.arrows = {from: arrows.from || noArrow, to: noArrow};
            }
            baseEdge.to = customMapVia.waypointId(edgeid, fromto, 0);

            var nodes = [];
            var segments = [];
            for (var i = 0; i < wps.length; i++) {
                var isLast = (i + 1 === wps.length);
                nodes.push({id: customMapVia.waypointId(edgeid, fromto, i), shape: "dot", size: dotSize || 0, x: wps[i][0], y: wps[i][1], label: '', physics: false});
                segments.push({
                    id: edgeid + "_" + fromto + "_seg_" + i,
                    from: customMapVia.waypointId(edgeid, fromto, i),
                    to: isLast ? midId : customMapVia.waypointId(edgeid, fromto, i + 1),
                    arrows: (isLast && toHead) ? {to: toHead, from: noArrow} : {to: noArrow, from: noArrow},
                    color: baseEdge.color,
                    width: baseEdge.width,
                    smooth: baseEdge.smooth,
                    font: baseEdge.font,
                    title: baseEdge.title,
                    scaling: baseEdge.scaling,
                    arrowStrikethrough: false,
                    physics: false,
                });
            }

            if (label && fromPos && midPos) {
                var pts = [{x: fromPos.x, y: fromPos.y}];
                wps.forEach(function (wp) {
                    pts.push({x: wp[0], y: wp[1]});
                });
                pts.push({x: midPos.x, y: midPos.y});
                var li = customMapVia.halfMidSubedge(pts);
                if (li > 0) {
                    baseEdge.label = '';
                    segments[li - 1].label = label;
                }
            }

            return {nodes: nodes, segments: segments};
        },

        // Position {x, y} of a map node from the map data API, or null if unknown
        nodePos: function (nodes, nodeid) {
            var node = nodes ? nodes[nodeid] : null;
            return node ? {x: node.x_pos, y: node.y_pos} : null;
        }
    };
</script>
@endonce
