{{-- Lazy loaded hover popups for vis.js maps. Usage: visPopups.attach(network, {ports: true}) after creating the network --}}
@once
<style>
    .vis-tooltip { display: none !important; }
    #vis-map-popup .panel { margin-bottom: 0; border: none; background: transparent; box-shadow: none; }
    #vis-map-popup .panel-body { padding: 0; }
</style>
<script type="text/javascript">
    var visPopups = {
        enabled: @json((bool) \App\Facades\LibrenmsConfig::get('web_mouseover', true)),
        baseUrl: @json(url('/')),
        cacheTtl: 60000,
        showDelay: 500,
        navbarHeight: 70,
        margin: 12,
        gap: 8,
        cache: {},
        showTimeout: null,
        hideTimeout: null,

        popupEl: function () {
            let popup = document.getElementById('vis-map-popup');
            if (!popup) {
                popup = document.createElement('div');
                popup.id = 'vis-map-popup';
                popup.className = 'tw:hidden tw:fixed tw:z-50 tw:w-min tw:max-w-[calc(100vw-24px)] tw:max-h-[calc(100vh-80px)] tw:overflow-y-auto tw:overflow-x-hidden tw:bg-white tw:dark:bg-dark-gray-300 tw:text-slate-800 tw:dark:text-white tw:border-2 tw:border-gray-200 tw:dark:border-dark-gray-200 tw:rounded-xl tw:shadow-2xl tw:text-sm';
                popup.addEventListener('mouseenter', () => clearTimeout(visPopups.hideTimeout));
                popup.addEventListener('mouseleave', () => visPopups.hide(200));
                document.body.appendChild(popup);
            }

            return popup;
        },

        /**
         * Show the popup for path, placed so it does not cover the hovered element
         * avoid: {left, top, right, bottom} viewport rect of the hovered element
         */
        show: function (path, avoid) {
            clearTimeout(visPopups.showTimeout);
            clearTimeout(visPopups.hideTimeout);

            visPopups.showTimeout = setTimeout(function () {
                const popup = visPopups.popupEl();
                const render = function (html) {
                    popup.innerHTML = html;
                    visPopups.position(popup, avoid);
                };
                popup.classList.remove('tw:hidden');

                const url = visPopups.baseUrl.replace(/\/$/, '') + path;
                const cached = visPopups.cache[url];
                if (cached && Date.now() - cached.time < visPopups.cacheTtl) {
                    render(cached.html);
                    return;
                }

                render('<div class="tw:p-4"><i class="fa-solid fa-circle-notch fa-spin"></i></div>');
                $.get(url, function (html) {
                    visPopups.cache[url] = {html: html, time: Date.now()};
                    render(html);
                }).fail(function () {
                    render('<div class="tw:p-3 tw:text-red-500">{{ __('Failed to load details.') }}</div>');
                });
            }, visPopups.showDelay);
        },

        hide: function (delay) {
            clearTimeout(visPopups.showTimeout);
            clearTimeout(visPopups.hideTimeout);
            visPopups.hideTimeout = setTimeout(function () {
                visPopups.popupEl().classList.add('tw:hidden');
            }, delay);
        },

        /**
         * Convert a point in the network's DOM coordinates to viewport coordinates
         */
        toViewport: function (network, domPoint) {
            const rect = network.canvas.frame.canvas.getBoundingClientRect();

            return {x: rect.left + domPoint.x, y: rect.top + domPoint.y};
        },

        /**
         * Viewport rect of a node, including its label
         */
        nodeRect: function (network, nodeId) {
            const box = network.getBoundingBox(nodeId);
            const topLeft = visPopups.toViewport(network, network.canvasToDOM({x: box.left, y: box.top}));
            const bottomRight = visPopups.toViewport(network, network.canvasToDOM({x: box.right, y: box.bottom}));

            return {left: topLeft.x, top: topLeft.y, right: bottomRight.x, bottom: bottomRight.y};
        },

        /**
         * Viewport rect around the mouse pointer of a vis event
         */
        pointerRect: function (network, params, padding = 20) {
            const point = visPopups.toViewport(network, params.pointer.DOM);

            return {left: point.x - padding, top: point.y - padding, right: point.x + padding, bottom: point.y + padding};
        },

        /**
         * Place the popup beside the avoid rect, preferring below/above. If it doesn't fit anywhere,
         * use the side with the most area and limit its size so it never covers the hovered element.
         */
        position: function (popup, avoid) {
            const margin = visPopups.margin;
            const gap = visPopups.gap;
            const minTop = visPopups.navbarHeight;
            const space = {
                below: window.innerHeight - margin - (avoid.bottom + gap),
                above: (avoid.top - gap) - minTop,
                right: window.innerWidth - margin - (avoid.right + gap),
                left: (avoid.left - gap) - margin,
            };

            popup.style.maxHeight = '';
            popup.style.maxWidth = '';
            const width = popup.offsetWidth;
            const height = popup.offsetHeight;
            const isVertical = (side) => side === 'below' || side === 'above';
            const clamp = (value, min, max) => Math.max(min, Math.min(value, max));
            const sides = Object.keys(space);
            const area = (side) => space[side] * (isVertical(side) ? window.innerWidth : window.innerHeight - minTop);

            const side = sides.find((side) => space[side] >= (isVertical(side) ? height : width))
                ?? sides.reduce((best, side) => area(side) > area(best) ? side : best);

            let left, top;
            if (isVertical(side)) {
                popup.style.maxHeight = Math.max(0, space[side]) + 'px';
                left = clamp((avoid.left + avoid.right - width) / 2, margin, window.innerWidth - width - margin);
                top = side === 'below' ? avoid.bottom + gap : avoid.top - gap - popup.offsetHeight;
            } else {
                popup.style.maxWidth = Math.max(0, space[side]) + 'px';
                top = clamp((avoid.top + avoid.bottom - height) / 2, minTop, window.innerHeight - popup.offsetHeight - margin);
                left = side === 'right' ? avoid.right + gap : avoid.left - gap - popup.offsetWidth;
            }

            popup.style.left = left + 'px';
            popup.style.top = top + 'px';
        },

        /**
         * options.ports: edge ids are "<port_id>.<remote_port_id>", show the port popup on hover
         * options.devicePath(nodeId) / options.portPath(edgeId): override the popup path, return null to show nothing
         */
        attach: function (network, options = {}) {
            if (!visPopups.enabled) {
                return;
            }

            const devicePath = options.devicePath ?? ((nodeId) => '/device/' + nodeId + '/popup');
            const portPath = options.portPath ?? (options.ports ? (edgeId) => '/port/' + String(edgeId).split('.')[0] + '/popup?from=-1d' : null);

            network.setOptions({interaction: {hover: true}});

            network.on('hoverNode', function (params) {
                const path = devicePath(params.node);
                if (path) {
                    visPopups.show(path, visPopups.nodeRect(network, params.node));
                }
            });
            network.on('blurNode', () => visPopups.hide(200));

            if (portPath) {
                network.on('hoverEdge', function (params) {
                    const path = portPath(params.edge);
                    if (path) {
                        visPopups.show(path, visPopups.pointerRect(network, params));
                    }
                });
                network.on('blurEdge', () => visPopups.hide(200));
            }

            // hide while clicking, dragging or zooming, so a pending popup can't intercept clicks
            network.canvas.frame.addEventListener('pointerdown', () => visPopups.hide(0));
            network.on('dragStart', () => visPopups.hide(0));
            network.on('zoom', () => visPopups.hide(0));
        },
    };
</script>
@endonce
