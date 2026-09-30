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

        show: function (path, x, y) {
            clearTimeout(visPopups.showTimeout);
            clearTimeout(visPopups.hideTimeout);

            visPopups.showTimeout = setTimeout(function () {
                const popup = visPopups.popupEl();
                popup.classList.remove('tw:hidden');

                const url = visPopups.baseUrl.replace(/\/$/, '') + path;
                const cached = visPopups.cache[url];
                if (cached && Date.now() - cached.time < visPopups.cacheTtl) {
                    popup.innerHTML = cached.html;
                    visPopups.position(popup, x, y);
                    return;
                }

                popup.innerHTML = '<div class="tw:p-4"><i class="fa-solid fa-circle-notch fa-spin"></i></div>';
                visPopups.position(popup, x, y);

                $.get(url, function (html) {
                    visPopups.cache[url] = {html: html, time: Date.now()};
                    popup.innerHTML = html;
                    visPopups.position(popup, x, y);
                }).fail(function () {
                    popup.innerHTML = '<div class="tw:p-3 tw:text-red-500">{{ __('Failed to load details.') }}</div>';
                });
            }, 150);
        },

        hide: function (delay) {
            clearTimeout(visPopups.showTimeout);
            clearTimeout(visPopups.hideTimeout);
            visPopups.hideTimeout = setTimeout(function () {
                visPopups.popupEl().classList.add('tw:hidden');
            }, delay);
        },

        position: function (popup, x, y) {
            const clearance = 30;
            const left = Math.max(12, Math.min(x - popup.offsetWidth / 2, window.innerWidth - popup.offsetWidth - 12));
            let top = y + clearance;
            if (top + popup.offsetHeight > window.innerHeight - 12) {
                // not enough room below, use whichever side has more space and keep it on screen
                const above = y - clearance - popup.offsetHeight;
                top = above > 70 || y > window.innerHeight / 2
                    ? Math.max(70, above)
                    : Math.max(70, window.innerHeight - popup.offsetHeight - 12);
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

            const canvasPoint = function (canvasPos) {
                const dom = network.canvasToDOM(canvasPos);
                const rect = network.canvas.frame.canvas.getBoundingClientRect();

                return [rect.left + dom.x, rect.top + dom.y];
            };

            network.on('hoverNode', function (params) {
                const pos = network.getPosition(params.node);
                visPopups.show('/device/' + params.node + '/popup' + (options.deviceQuery || ''), ...canvasPoint(pos));
            });
            network.on('blurNode', () => visPopups.hide(200));

            if (options.ports) {
                network.on('hoverEdge', function (params) {
                    const edge = network.body.edges[params.edge];
                    const portId = String(params.edge).split('.')[0];
                    const pos = edge ? {x: (edge.from.x + edge.to.x) / 2, y: (edge.from.y + edge.to.y) / 2} : network.getViewPosition();
                    visPopups.show('/port/' + portId + '/popup' + (options.portQuery ?? '?from=-1d'), ...canvasPoint(pos));
                });
                network.on('blurEdge', () => visPopups.hide(200));
            }

            // hide while dragging or zooming
            network.on('dragStart', () => visPopups.hide(0));
            network.on('zoom', () => visPopups.hide(0));
        },
    };
</script>
@endonce
