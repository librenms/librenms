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

        showDelay: 500,
        navbarHeight: 70,
        margin: 12,
        gap: 8,

        /**
         * Show the popup for path, placed so it does not cover the hovered element
         * avoid: {left, top, right, bottom} viewport rect of the hovered element
         */
        show: function (path, avoid) {
            clearTimeout(visPopups.showTimeout);
            clearTimeout(visPopups.hideTimeout);

            visPopups.showTimeout = setTimeout(function () {
                const popup = visPopups.popupEl();
                popup.classList.remove('tw:hidden');

                const url = visPopups.baseUrl.replace(/\/$/, '') + path;
                const cached = visPopups.cache[url];
                if (cached && Date.now() - cached.time < visPopups.cacheTtl) {
                    popup.innerHTML = cached.html;
                    visPopups.position(popup, avoid);
                    return;
                }

                popup.innerHTML = '<div class="tw:p-4"><i class="fa-solid fa-circle-notch fa-spin"></i></div>';
                visPopups.position(popup, avoid);

                $.get(url, function (html) {
                    visPopups.cache[url] = {html: html, time: Date.now()};
                    popup.innerHTML = html;
                    visPopups.position(popup, avoid);
                }).fail(function () {
                    popup.innerHTML = '<div class="tw:p-3 tw:text-red-500">{{ __('Failed to load details.') }}</div>';
                });
            }, visPopups.showDelay);
        },

        /**
         * Show the device popup for a vis node, avoiding the node (including its label)
         */
        showNode: function (network, nodeId, path) {
            visPopups.show(path, visPopups.nodeRect(network, nodeId));
        },

        /**
         * Show the port popup for a vis edge, avoiding the area around the mouse pointer
         */
        showEdge: function (network, params, path) {
            const rect = network.canvas.frame.canvas.getBoundingClientRect();
            const x = rect.left + params.pointer.DOM.x;
            const y = rect.top + params.pointer.DOM.y;
            const pad = 20;

            visPopups.show(path, {left: x - pad, top: y - pad, right: x + pad, bottom: y + pad});
        },

        hide: function (delay) {
            clearTimeout(visPopups.showTimeout);
            clearTimeout(visPopups.hideTimeout);
            visPopups.hideTimeout = setTimeout(function () {
                visPopups.popupEl().classList.add('tw:hidden');
            }, delay);
        },

        nodeRect: function (network, nodeId) {
            const rect = network.canvas.frame.canvas.getBoundingClientRect();
            const box = network.getBoundingBox(nodeId);
            const topLeft = network.canvasToDOM({x: box.left, y: box.top});
            const bottomRight = network.canvasToDOM({x: box.right, y: box.bottom});

            return {
                left: rect.left + topLeft.x,
                top: rect.top + topLeft.y,
                right: rect.left + bottomRight.x,
                bottom: rect.top + bottomRight.y,
            };
        },

        /**
         * Place the popup next to the avoid rect on the side with the most room (preferring below/above),
         * limiting its size to that room so it never covers the hovered element.
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
            const clamp = (value, min, max) => Math.max(min, Math.min(value, max));

            let side;
            if (space.below >= height) {
                side = 'below';
            } else if (space.above >= height) {
                side = 'above';
            } else if (space.right >= width || space.left >= width) {
                side = space.right >= width ? 'right' : 'left';
            } else {
                // nothing fits completely, use the side with the most area and shrink to fit
                const area = {
                    below: space.below * window.innerWidth,
                    above: space.above * window.innerWidth,
                    right: space.right * (window.innerHeight - minTop),
                    left: space.left * (window.innerHeight - minTop),
                };
                side = Object.keys(area).reduce((best, key) => area[key] > area[best] ? key : best);
            }

            let left, top;
            if (side === 'below' || side === 'above') {
                popup.style.maxHeight = Math.max(0, space[side]) + 'px';
                const centerX = (avoid.left + avoid.right) / 2;
                left = clamp(centerX - width / 2, margin, window.innerWidth - width - margin);
                top = side === 'below' ? avoid.bottom + gap : avoid.top - gap - popup.offsetHeight;
            } else {
                popup.style.maxWidth = Math.max(0, space[side]) + 'px';
                const centerY = (avoid.top + avoid.bottom) / 2;
                top = clamp(centerY - height / 2, minTop, window.innerHeight - popup.offsetHeight - margin);
                left = side === 'right' ? avoid.right + gap : avoid.left - gap - popup.offsetWidth;
            }

            popup.style.left = left + 'px';
            popup.style.top = top + 'px';
        },

        /**
         * options.ports: edge ids are "<port_id>.<remote_port_id>", show the port popup on hover
         * options.deviceQuery / options.portQuery: query string appended to the popup request
         */
        attach: function (network, options = {}) {
            if (!visPopups.enabled) {
                return;
            }

            network.setOptions({interaction: {hover: true}});

            network.on('hoverNode', function (params) {
                visPopups.showNode(network, params.node, '/device/' + params.node + '/popup' + (options.deviceQuery || ''));
            });
            network.on('blurNode', () => visPopups.hide(200));

            if (options.ports) {
                network.on('hoverEdge', function (params) {
                    const portId = String(params.edge).split('.')[0];
                    visPopups.showEdge(network, params, '/port/' + portId + '/popup' + (options.portQuery ?? '?from=-1d'));
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
