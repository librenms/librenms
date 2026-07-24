<nav class="navbar navbar-default {{ $navbar }} navbar-sticky-top" role="navigation">
        <div class="navbar-header">
            <a class="navbar-brand" href="{{ route('home') }}">
                <x-logo responsive="lg" class="tw:h-full tw:max-w-[170px]" />
            </a>
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navHeaderCollapse">
                <span class="sr-only">{{ __('Toggle navigation') }}</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="navHeaderCollapse" style="max-height: calc(100vh - 50px)">
            <ul class="nav navbar-nav">
{{-- Overview --}}
                <li class="dropdown">
                    <a href="{{ url('overview') }}" class="dropdown-toggle" data-hover="dropdown"
                       data-toggle="dropdown"><i class="fa fa-home fa-fw fa-lg fa-nav-icons"
                                                 aria-hidden="true"></i> <span
                            class="tw:md:hidden tw:2xl:inline-block">{{ __('Overview') }}</span></a>
                    <ul class="dropdown-menu multi-level" role="menu">
                        <li class="dropdown-submenu">
                            <a href="{{ route('overview') }}"><i class="fa fa-tv fa-fw fa-lg" aria-hidden="true"></i> {{ __('Dashboard') }}</a>
                            <ul class="dropdown-menu scrollable-menu">
                                @foreach($dashboards as $dashboard)
                                <li><a href="{{ route('overview', ['dashboard' => $dashboard->dashboard_id]) }}"><i class="fa fa-tv fa-fw fa-lg" aria-hidden="true"></i> {{ $dashboard->dashboard_name }}</a></li>
                                @endforeach
                                <li role="presentation" class="divider"></li>
                                <li>
                                    <a onclick="toggleDashboardEditor()">
                                        <i class="fa fa-bar-chart fa-fw fa-lg" aria-hidden="true"></i>
                                        <span id="toggle-dashboard-editor-text">@if ($hide_dashboard_editor) {{ __('Show Dashboard Editor') }} @else {{ __('Hide Dashboard Editor') }}@endif</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li role="presentation" class="divider"></li>
                        @if(Gate::allows('plugin.admin') || $has_v1_plugins || $has_v2_plugins)
                        <li class="dropdown-submenu">
                            <a><i class="fa fa-plug fa-fw fa-lg" aria-hidden="true"></i> {{ __('Plugins') }}</a>
                            <ul class="dropdown-menu">
                                {!! $v1_plugin_menu !!}
                                @foreach($menu_hooks as [$view, $data])
                                    <li>@include($view, $data)</li>
                                @endforeach
                                @can('plugin.admin')
                                @if($has_v1_plugins || $has_v2_plugins)
                                    <li role="presentation" class="divider"></li>
                                @endif
                                <li>
                                    <a href="{{ route('plugin.admin') }}">
                                        <i class="fa fa-lock fa-fw fa-lg" aria-hidden="true"></i>{{ __('Plugin Admin') }}
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        @endif
                        <li class="dropdown-submenu">
                            <a><i class="fa fa-wrench fa-fw fa-lg"
                                                               aria-hidden="true"></i> {{ __('Tools') }}</a>
                            <ul class="dropdown-menu">
                                @can('tools.ripe')
                                <li><a href="{{ url('ripenccapi') }}"><i class="fa fa-star fa-fw fa-lg"
                                                                         aria-hidden="true"></i> {{ __('RIPE NCC API') }}
                                    </a></li>
                                @endcan
                                @config('smokeping.integration')
                                <li><a href="{{ \App\Facades\LibrenmsConfig::get('smokeping.url') }}"><i class="fa fa-line-chart fa-fw fa-lg"
                                                                       aria-hidden="true"></i> {{ __('Smokeping') }}</a>
                                </li>
                                @endconfig
                                @config('mac_oui.enabled')
                                    @can('tools.oui')
                                    <li><a href="{{ route('tool.oui-lookup') }}"><i class="fa fa-magnifying-glass fa-fw fa-lg"
                                                                                                  aria-hidden="true"></i> {{ __('tools.oui.title') }}</a>
                                    </li>
                                    @endcan
                                @endconfig
                                @config('oxidized.enabled')
                                <li><a href="{{ url('oxidized') }}"><i class="fa fa-stack-overflow fa-fw fa-lg"
                                                                       aria-hidden="true"></i> {{ __('Oxidized') }}</a>
                                </li>
                                @endconfig
                                @can('viewAny', \App\Models\SslCertificate::class)
                                <li><a href="{{ url('ssl-certificates') }}"><i class="fa fa-lock fa-fw fa-lg fa-nav-icons"
                                    aria-hidden="true"></i> {{ __('SSL Certificates') }}</a>
                                </li>
                                @endcan
                            </ul>
                        </li>
                        <li role="presentation" class="divider"></li>
                        <li><a href="{{ url('eventlog') }}"><i class="fa fa-bookmark fa-fw fa-lg"
                                                               aria-hidden="true"></i> {{ __('Eventlog') }}</a></li>
                        @config('enable_syslog')
                        <li><a href="{{ url('syslog') }}"><i class="fa fa-clone fa-fw fa-lg"
                                                             aria-hidden="true"></i> {{ __('syslog.title') }}</a></li>
                        @endconfig
                        @config('graylog.server')
                        <li><a href="{{ url('graylog') }}"><i class="fa fa-clone fa-fw fa-lg"
                                                              aria-hidden="true"></i> {{ __('Graylog') }}</a></li>
                        @endconfig

                        <li><a href="{{ route('inventory') }}"><i class="fa fa-cube fa-fw fa-lg"
                                                                aria-hidden="true"></i> {{ __('Inventory') }}</a></li>
                        @if($package_count)
                            <li><a href="{{ url('search/search=packages') }}"><i class="fa fa-archive fa-fw fa-lg"
                                                                                 aria-hidden="true"></i> {{ __('Packages') }}
                                </a></li>
                        @endif

                        <li role="presentation" class="divider"></li>
                        <li><a href="{{ url('search/search=ipv4') }}"><i class="fa fa-search fa-fw fa-lg"
                                                                         aria-hidden="true"></i> {{ __('IPv4 Address') }}
                            </a></li>
                        <li><a href="{{ url('search/search=ipv6') }}"><i class="fa fa-search fa-fw fa-lg"
                                                                         aria-hidden="true"></i> {{ __('IPv6 Address') }}
                            </a></li>
                        <li><a href="{{ url('search/search=mac') }}"><i class="fa fa-search fa-fw fa-lg"
                                                                        aria-hidden="true"></i> {{ __('MAC Address') }}</a>
                        </li>
                        <li><a href="{{ url('search/search=arp') }}"><i class="fa fa-search fa-fw fa-lg"
                                                                        aria-hidden="true"></i> {{ __('ARP Tables') }}</a>
                        </li>
                        <li><a href="{{ url('search/search=fdb') }}"><i class="fa fa-search fa-fw fa-lg"
                                                                        aria-hidden="true"></i> {{ __('FDB Tables') }}</a>
                        </li>
                    </ul>
                </li>
{{-- Devices --}}
            @if(! $no_devices_added || Gate::allows('create', \App\Models\Device::class))
                <li class="dropdown">
                    <a href="{{ route('devices') }}" class="dropdown-toggle" data-hover="dropdown"
                       data-toggle="dropdown"><i class="fa fa-server fa-fw fa-lg fa-nav-icons"
                                                 aria-hidden="true"></i> <span>{{ __('Devices') }}</span></a>
                    <ul class="dropdown-menu">
                    @if($no_devices_added)
                    <li><a href="#"><i class="fa fa-server fa-fw fa-lg" aria-hidden="true"></i> {{ __('No Devices') }}</a>
                    @else
                    <li @class(['dropdown-submenu' => $device_types->isNotEmpty()])><a href="{{ route('devices') }}"><i class="fa fa-server fa-fw fa-lg" aria-hidden="true"></i> {{ __('All Devices') }}</a>
                        @if($device_types->isNotEmpty())
                        <ul class="dropdown-menu scrollable-menu">
                        @foreach($device_types as $type => $icon)
                            <li><a href="{{ route('devices', ['filter' => ['type' => ['eq' => $type]]]) }}"><i class="fa fa-{{ $icon }} fa-fw fa-lg" aria-hidden="true"></i> {{ ucfirst($type) }}</a></li>
                        @endforeach
                        </ul>
                        @endif
                    </li>
                    @endif

                    @if($device_groups->isNotEmpty())
                            <li class="dropdown-submenu"><a><i class="fa fa-th fa-fw fa-lg"
                                                                        aria-hidden="true"></i> {{ __('Device Groups') }}
                                </a>
                            <ul class="dropdown-menu scrollable-menu">
                            @foreach($device_groups as $group)
                                <li><a href="{{ route('devices', ['filter' => ['groups.id' => ['eq' => $group->id]]]) }}" title="{{ $group->desc }}"><i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> {{ ucfirst($group->name) }}</a></li>
                            @endforeach
                            </ul>
                        </li>
                    @endif
                    @can('viewAny', \App\Models\Location::class)
                    @if($locations->isNotEmpty())
                        <li class="dropdown-submenu">
                            <a href="{{ url('locations') }}"><i class="fa fa-map-marker fa-fw fa-lg" aria-hidden="true"></i> {{ __('Geo Locations') }}</a>
                            <ul class="dropdown-menu scrollable-menu">
                                <li><a href="{{ url('locations') }}"><i class="fa fa-map-marker fa-fw fa-lg" aria-hidden="true"></i> {{ __('All Locations') }}</a></li>
                            @foreach($locations as $location)
                                    <li><a href="{{ route('devices', ['filter' => ['location_id' => ['eq' => $location->id]]]) }}"><i class="fa fa-building fa-fw fa-lg" aria-hidden="true"></i> {{ $location->display() }}</a></li>
                            @endforeach
                            </ul>
                        </li>
                    @endif
                    @endcan
                        @can('viewAny', \App\Models\DeviceOutage::class)
                        <li role="presentation" class="divider"></li>
                        <li><a href="{{ route('outages') }}"><i class="fa fa-exclamation-triangle fa-fw fa-lg"
                                                              aria-hidden="true"></i> {{ __('Outages') }}</a></li>
                        @endcan
                        @if($show_device_extra_divider)
                        <li role="presentation" class="divider"></li>
                        @endif
                        @can('viewAny', \App\Models\DeviceGroup::class)
                            <li><a href="{{ url('device-groups') }}"><i class="fa fa-th fa-fw fa-lg"
                                                                        aria-hidden="true"></i> {{ __('Manage Groups') }}
                                </a></li>
                        @endcan
                        @can('device.update')
                        <li><a href="{{ url('device-dependencies') }}"><i class="fa fa-group fa-fw fa-lg"></i> {{ __('Device Dependencies') }}</a></li>
                        @endcan
                        @if($show_vmwinfo)
                            <li><a href="{{ url('vminfo') }}"><i
                                        class="fa fa-cog fa-fw fa-lg"></i> {{ __('Virtual Machines') }}</a></li>
                        @endif
                        @canany(['device.create', 'device.delete'])
                        <li role="presentation" class="divider"></li>
                        @endcanany
                        @can('device.create')
                        <li><a href="{{ url('addhost') }}"><i class="fa fa-plus fa-fw fa-lg"
                                                              aria-hidden="true"></i> {{ __('Add Device') }}</a></li>
                        @endcan
                        @can('device.delete')
                        <li><a href="{{ route('device.delete') }}"><i class="fa fa-trash fa-fw fa-lg"
                                                              aria-hidden="true"></i> {{ __('Delete Device') }}</a></li>
                        @endcan
                    </ul>
                </li>
            @endif
{{-- Maps --}}
                <li class="dropdown">
                    <a href="{{ url('services') }}" class="dropdown-toggle" data-hover="dropdown"
                       data-toggle="dropdown"><i class="fa fa-map fa-fw fa-lg fa-nav-icons"
                                                 aria-hidden="true"></i> <span
                            class="tw:md:hidden tw:lg:inline-block">{{ __('Maps') }}</span></a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ url('availability-map') }}"><i class="fa fa-arrow-circle-up fa-fw fa-lg"
                                                                       aria-hidden="true"></i> {{ __('Availability') }}
                            </a></li>
                        @if($device_dependencies)
                        <li><a href="{{ url('maps/devicedependency') }}"><i class="fa fa-chain fa-fw fa-lg"
                                                                            aria-hidden="true"></i> {{ __('Device Dependency') }}</a></li>
                        @endif
                        @if($device_group_dependencies)
                            <li class="dropdown-submenu"><a><i class="fa fa-chain fa-fw fa-lg"
                                                               aria-hidden="true"></i> {{ __('Device Groups Dependencies') }}
                                </a>
                                <ul class="dropdown-menu scrollable-menu">
                                    @foreach($device_groups as $group)
                                        <li><a href="{{ url("maps/devicedependency?group=$group->id") }}" title="{{ $group->desc }}"><i class="fa fa-chain fa-fw fa-lg" aria-hidden="true"></i>
                                                {{ ucfirst($group->name) }}
                                            </a></li>
                                    @endforeach
                                </ul></li>
                        @endif
                        @if($links)
                        <li><a href="{{ url('map') }}"><i class="fa fa-sitemap fa-fw fa-lg"
                                                          aria-hidden="true"></i> {{ __('Network') }}</a></li>
                        @endif
                        <li><a href="{{ url('fullscreenmap') }}"><i class="fa fa-expand fa-fw fa-lg"
                                                                    aria-hidden="true"></i> {{ __('Geographical') }}
                            </a></li>
                        <li><a href="{{ route('maps.custom.index') }}">
                                <i class="fa fa-list fa-fw fa-lg" aria-hidden="true"></i> {{ __('Custom Maps') }}
                            </a></li>
                        @if($device_groups->isNotEmpty())
                            <li class="dropdown-submenu"><a><i class="fa fa-th fa-fw fa-lg"
                                                               aria-hidden="true"></i> {{ __('Device Groups Maps') }}
                                </a>
                                <ul class="dropdown-menu scrollable-menu">
                                    @foreach($device_groups as $group)
                                        <li><a href="{{ url("map/group=$group->id") }}" title="{{ $group->desc }}"><i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i>
                                                {{ ucfirst($group->name) }}
                                            </a></li>
                                    @endforeach
                                </ul></li>
                        @endif

                        @if($custommaps_groups->isNotEmpty() || $custommaps->isNotEmpty())
                            <li role="presentation" class="divider"></li>
                            @foreach($custommaps as $map)
                                <li><a href="{{ route('maps.custom.show', ['map' => $map->custom_map_id]) }}">
                                        <i class="fa fa-map-marked fa-fw fa-lg" aria-hidden="true"></i>
                                        {{ $map->name }}
                                    </a></li>
                            @endforeach
                        @if($custommaps_groups->count() < 20)
                            @foreach($custommaps_groups as $map_group => $group_maps)
                            <li class="dropdown-submenu">
                                <a href="{{ route('maps.custom.list', ['group' => $map_group]) }}"><i class="fa fa-map fa-fw fa-lg" aria-hidden="true"></i> {{ $map_group  }}</a>
                                <ul class="dropdown-menu scrollable-menu">
                                @foreach($group_maps as $map)
                                <li><a href="{{ route('maps.custom.show', ['map' => $map->custom_map_id]) }}"><i class="fa fa-map-marked fa-fw fa-lg" aria-hidden="true"></i>
                                    {{ $map->name }}
                                </a></li>
                                @endforeach
                                </ul>
                            </li>
                            @endforeach
                        @else
                            <li class="dropdown-submenu"><a href="{{ route('maps.custom.list') }}"><i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> {{__('Custom Map Groups') }}</a>
                                <ul class="dropdown-menu scrollable-menu">
                                    @foreach($custommaps_groups as $map_group => $group_maps)
                                        <li><a href="{{ route('maps.custom.list', ['group' => $map_group]) }}"><i class="fa fa-map-marked fa-fw fa-lg" aria-hidden="true"></i>
                                                {{ $map_group }}
                                            </a></li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                        @endif
                        @can('custom-map.update')
                        <li role="presentation" class="divider"></li>
                        <li><a href="{{ route('maps.custom.index') }}">
                            <i class="fa fa-pen fa-fw fa-lg" aria-hidden="true"></i> {{ __('Custom Map Editor') }}
                        </a></li>
                        @if(Route::is('maps.custom.show'))
                        <li><a href="{{ route('maps.custom.edit', ['map' => Route::current()->parameter('map')]) }}">
                            <i class="fa fa-pen-to-square fa-fw fa-lg" aria-hidden="true"></i> {{ __('Edit Current Map') }}
                        </a></li>
                        @endif
                        <li><a href="{{ route('maps.nodeimage.index') }}">
                            <i class="fa fa-image fa-fw fa-lg" aria-hidden="true"></i> {{ __('Custom Node Image Manager') }}
                        </a></li>
                        @endcan

                    </ul>
                </li>
{{-- Ports --}}
            @if($show_ports_menu)
                <li class="dropdown">
                    <a href="{{ route('ports') }}" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i
                            class="fa fa-link fa-fw fa-lg fa-nav-icons" aria-hidden="true"></i> <span
                            class="tw:md:hidden tw:lg:inline-block">{{ __('Ports') }}</span></a>
                    <ul class="dropdown-menu">
                        @can('viewAny', \App\Models\Port::class)
                        <li><a href="{{ route('ports') }}"><i class="fa fa-link fa-fw fa-lg"
                                                            aria-hidden="true"></i> {{ __('All Ports') }}</a></li>

                        @if($port_counts['errored'] > 0)
                            <li><a href="{{ route('ports', ['view' => 'detail', 'filter' => ['errors' => ['eq' => '1']]]) }}"><i class="fa fa-exclamation-circle fa-fw fa-lg"
                                                                           aria-hidden="true"></i> {{ __('Errored :port_count', ['port_count' => $port_counts['errored']]) }}
                                </a></li>
                        @endif

                        @if($port_counts['ignored'] > 0)
                            <li><a href="{{ route('ports', ['view' => 'detail', 'filter' => ['ignore' => ['eq' => '1']]]) }}"><i class="fa fa-question-circle fa-fw fa-lg"
                                                                           aria-hidden="true"></i> {{ __('Ignored :port_count', ['port_count' => $port_counts['ignored']]) }}
                                </a></li>
                        @endif
                        @endcan

                        @can('viewAny', \App\Models\Vlan::class)
                        <li><a href="{{ route('vlans.index') }}"><i class="fa fa-tasks fa-fw fa-lg"
                                                            aria-hidden="true"></i> {{ __('VLANs') }}</a></li>
                        @endcan

                        @can('viewAny', \App\Models\Bill::class)
                        <li><a href="{{ url('bills') }}"><i class="fa fa-money fa-fw fa-lg"
                                                            aria-hidden="true"></i> {{ __('Traffic Bills') }}</a></li>
                        @endCan

                        @if($port_counts['pseudowire'] > 0)
                            <li><a href="{{ url('pseudowires') }}"><i class="fa fa-arrows-alt fa-fw fa-lg"
                                                                      aria-hidden="true"></i> {{ __('Pseudowires') }}</a>
                            </li>
                        @endif

                        @can('viewAny', \App\Models\Port::class)
                            <li><a href="{{ route('port-security.index') }}"><i class="fa fa-shield fa-fw fa-lg"
                                                                         aria-hidden="true"></i> {{ __('Port Security') }}</a>
                            </li>

                            @if($port_nac)
                                <li role="presentation" class="divider"></li>
                                <li><a href="{{ url('nac') }}"><i class="fa fa-lock fa-fw fa-lg"
                                                                  aria-hidden="true"></i> NAC</a></li>
                            @endif

                            @if($port_groups_exist)
                                <li role="presentation" class="divider"></li>
                                @config('int_customers')
                                <li><a href="{{ url('customers') }}"><i class="fa fa-users fa-fw fa-lg"
                                                                        aria-hidden="true"></i> {{ __('Customers') }}</a>
                                </li>
                                @endconfig
                                @config('int_l2tp')
                                <li><a href="{{ route('porttype.graph', ['l2tp']) }}"><i class="fa fa-link fa-fw fa-lg"
                                                                               aria-hidden="true"></i> {{ __('L2TP') }}</a>
                                </li>
                                @endconfig
                                @config('int_transit')
                                <li><a href="{{ route('porttype.graph', ['transit']) }}"><i class="fa fa-truck fa-fw fa-lg"
                                                                                  aria-hidden="true"></i> {{ __('Transit') }}
                                    </a></li>
                                @endconfig
                                @config('int_peering')
                                <li><a href="{{ route('porttype.graph', ['peering']) }}"><i class="fa fa-handshake-o fa-fw fa-lg"
                                                                                  aria-hidden="true"></i> {{ __('Peering') }}
                                    </a></li>
                                @endconfig
                                @if(\App\Facades\LibrenmsConfig::get('int_peering') && \App\Facades\LibrenmsConfig::get('int_transit'))
                                    <li><a href="{{ route('porttype.graph', ['peering,transit']) }}"><i
                                                class="fa fa-rocket fa-fw fa-lg"
                                                aria-hidden="true"></i> {{ __('Peering + Transit') }}</a></li>
                                @endif
                                @config('int_core')
                                <li><a href="{{ route('porttype.graph', ['core']) }}"><i class="fa fa-code-fork fa-fw fa-lg"
                                                                               aria-hidden="true"></i> {{ __('Core') }}</a>
                                </li>
                                @endconfig
                                @foreach($custom_port_descr as $custom_descr)
                                    <li><a href="{{ route('porttype.graph', [urlencode($custom_descr['name'])]) }}"><i class="fa {{$custom_descr['icon']}} fa-fw fa-lg" aria-hidden="true"></i> {{ ucwords($custom_descr['name']) }}</a></li>
                                @endforeach
                            @endif

                            <li role="presentation" class="divider"></li>
                            @can('manage', \App\Models\PortGroup::class)
                            <li><a href="{{ url('port-groups') }}"><i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> {{ __('Manage Groups') }} </a></li>
                            @endcan
                            @if($port_groups->isNotEmpty())
                                <li class="dropdown-submenu">
                                <a href="{{ url('port-groups') }}"><i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> {{ __('Port Groups') }}</a>
                                <ul class="dropdown-menu scrollable-menu">
                                @foreach($port_groups as $group)
                                    <li><a href="{{ route('ports', ['filter' => ['groups.id' => ['eq' => $group->id]]]) }}" title="{{ $group->desc }}"><i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> {{ ucfirst($group->name) }}</a></li>
                                @endforeach
                                </ul>
                                </li>
                            @endif

                            <li role="presentation" class="divider"></li>
                            @if($port_counts['alerted'])
                                <li><a href="{{ route('ports', ['view' => 'detail', 'errors' => 1]) }}"><i
                                            class="fa fa-exclamation-circle fa-fw fa-lg"
                                            aria-hidden="true"></i> {{ __('Alerts :port_count', ['port_count' => $port_counts['alerted']]) }}
                                    </a></li>
                            @endif

                            <li><a href="{{ route('ports', ['view' => 'detail', 'filter' => ['state' => ['eq' => 'down']]]) }}"><i class="fa fa-arrow-circle-down fa-fw fa-lg"
                                                                           aria-hidden="true"></i> {{ __('Down :port_count', ['port_count' => $port_counts['down']]) }}
                                </a></li>
                            <li><a href="{{ route('ports', ['view' => 'detail', 'filter' => ['state' => ['eq' => 'shutdown'], 'disabled' => ['eq' => '0'], 'ignore' => ['eq' => '0'], 'deleted' => ['eq' => '0']]]) }}"><i
                                        class="fa fa-arrow-circle-o-down fa-fw fa-lg"
                                        aria-hidden="true"></i> {{ __('Disabled :port_count', ['port_count' => $port_counts['shutdown']]) }}
                                </a></li>

                            @can('port.delete')
                            @if($port_counts['deleted'])
                                <li><a href="{{ route('ports', ['view' => 'detail', 'filter' => ['deleted' => ['eq' => '1']]]) }}"><i class="fa fa-minus-circle fa-fw fa-lg"
                                                                                aria-hidden="true"></i> {{ __('Deleted :port_count', ['port_count' => $port_counts['deleted']]) }}
                                    </a></li>
                            @endif
                            @endcan
                        @endcan
                    </ul>
                </li>
            @endif
{{-- Sensors --}}
                @if($show_health_menu)
                <li class="dropdown">
                    <a href="{{ url('health') }}" class="dropdown-toggle" data-hover="dropdown"
                       data-toggle="dropdown"><i class="fa fa-heartbeat fa-fw fa-lg fa-nav-icons"
                                                 aria-hidden="true"></i> <span class="tw:md:hidden tw:lg:inline-block">{{ __('Health') }}</span></a>
                    <ul class="dropdown-menu">
                        @can('viewAny', \App\Models\Sensor::class)
                        <li><a href="{{ url('health/metric=all?status=alert') }}"><i class="fas fa-bell fa-fw fa-lg"
                                                                            aria-hidden="true"></i> {{ __('Alerts') }}</a>
                        </li>
                        <li role="presentation" class="divider"></li>
                        @endcan
                        @can('viewAny', \App\Models\Mempool::class)
                        <li><a href="{{ url('health/metric=mempool') }}"><i class="fas fa-memory fa-fw fa-lg"
                                                                            aria-hidden="true"></i> {{ __('Memory') }}</a>
                        </li>
                        @endcan
                        @can('viewAny', \App\Models\Processor::class)
                        <li><a href="{{ url('health/metric=processor') }}"><i class="fa fa-microchip fa-fw fa-lg"
                                                                              aria-hidden="true"></i> {{ __('Processor') }}
                            </a></li>
                        @endcan
                        @can('viewAny', \App\Models\Storage::class)
                        <li><a href="{{ url('health/metric=storage') }}"><i class="fa fa-database fa-fw fa-lg"
                                                                            aria-hidden="true"></i> {{ __('Storage') }}</a>
                        </li>
                        @endcan

                        @can('viewAny', \App\Models\Sensor::class)
                        @foreach($sensor_menu as $sensor_menu_group)
                            @foreach($sensor_menu_group as $sensor_menu_entry)
                                @if($loop->first)
                                    <li role="presentation" class="divider"></li>
                                @endif
                                <li><a href="{{ url('health/metric=' . $sensor_menu_entry['class']) }}"><i class="fa fa-{{ $sensor_menu_entry['icon'] }} fa-fw fa-lg" aria-hidden="true"></i> {{ $sensor_menu_entry['descr'] }}</a></li>
                            @endforeach
                        @endforeach
                        @endcan
                    </ul>
                </li>
                @endif
{{-- Wireless --}}
                @can('viewAny', \App\Models\WirelessSensor::class)
                @if($wireless_menu->isNotEmpty())
                    <li class="dropdown">
                        <a href="{{ url('wireless') }}" class="dropdown-toggle" data-hover="dropdown"
                           data-toggle="dropdown"><i class="fa fa-wifi fa-fw fa-lg fa-nav-icons"
                                                     aria-hidden="true"></i> <span
                                class="tw:md:hidden tw:2xl:inline-block">{{ __('wireless.title') }}</span></a>
                        <ul class="dropdown-menu">
                        @foreach($wireless_menu as $wireless_menu_entry)
                                <li><a href="{{ url('wireless/metric=' . $wireless_menu_entry->sensor_class->value) }}"><i class="fa fa-{{ $wireless_menu_entry->icon() }} fa-fw fa-lg" aria-hidden="true"></i> {{ $wireless_menu_entry->classDescr() }}</a></li>
                        @endforeach
                        </ul>
                    </li>
                @endif
                @endcan
{{-- Services --}}
        @config('show_services')
            @can('viewAny', \App\Models\Service::class)
            <li class="dropdown">
                <a href="{{ url('services') }}" class="dropdown-toggle" data-hover="dropdown"
                   data-toggle="dropdown"><i class="fa fa-cogs fa-fw fa-lg fa-nav-icons"
                                             aria-hidden="true"></i> <span
                        class="tw:md:hidden tw:2xl:inline-block">{{ __('Services') }}</span></a>
                <ul class="dropdown-menu">
                    <li><a href="{{ url('services') }}"><i class="fa fa-cogs fa-fw fa-lg" aria-hidden="true"></i> {{ __('All Services') }}</a>
                    </li>
                    @can('viewAny', \App\Models\ServiceTemplate::class)
                    <li><a href="{{ route('services.templates.index') }}"><span class="fa-stack" aria-hidden="true" style="font-size: 12px">
                                  <i class="fa fa-square fa-stack-2x"></i>
                                  <i class="fa fa-cogs fa-stack-1x fa-inverse"></i>
                                </span> {{ __('Services Templates') }}</a>
                    </li>
                    @endcan
                    @if($service_counts['warning'] || $service_counts['critical'])
                        <li role="presentation" class="divider"></li>
                        @if($service_counts['warning'])
                            <li><a href="{{ url('services/state=warning') }}"><i
                                        class="fa fa-bell text-warning fa-fw fa-lg"
                                        aria-hidden="true"></i> {{ __('Warning :service_count', ['service_count' => $service_counts['warning']]) }}
                                </a></li>
                        @endif
                        @if($service_counts['critical'])
                            <li><a href="{{ url('services/state=critical') }}"><i
                                        class="fa fa-bell text-danger fa-fw fa-lg"
                                        aria-hidden="true"></i> {{ __('Critical :service_count', ['service_count' => $service_counts['critical']]) }}
                                </a></li>
                        @endif
                    @endif
                    @can('service.create')
                    <li role="presentation" class="divider"></li>
                    <li><a href="{{ url('addsrv') }}"><i class="fa fa-plus fa-fw fa-lg"
                                                         aria-hidden="true"></i> {{ __('Add Service') }}</a></li>
                    @endcan
                </ul>
            </li>
            @endcan
        @endconfig
{{-- App --}}
                @if(Gate::allows('viewAny', \App\Models\Application::class) && $app_menu->isNotEmpty())
                    <li class="dropdown">
                        <a href="{{ url('apps') }}" class="dropdown-toggle" data-hover="dropdown"
                           data-toggle="dropdown"><i class="fa fa-tasks fa-fw fa-lg fa-nav-icons"
                                                     aria-hidden="true"></i> <span
                                class="tw:md:hidden tw:2xl:inline-block">{{ __('Apps') }}</span></a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ url('apps') }}"><i class="fa fa-object-group fa-fw fa-lg"
                                                               aria-hidden="true"></i> {{ __('Overview') }}</a></li>
                            @foreach($app_menu as $app_type => $app_instances)
                                @if($app_instances->filter->app_instance->isNotEmpty())
                                    <li class="dropdown-submenu">
                                        <a href="{{ url('apps/app=' . $app_type) }}"><i class="fa fa-server fa-fw fa-lg" aria-hidden="true"></i> {{ $app_instances->first()->displayName() }}</a>
                                        <ul class="dropdown-menu scrollable-menu">
                                            @foreach($app_instances as $app_instance)
                                            <li><a href="{{ url("apps/app=$app_type/instance=$app_instance->app_instance") }}"><i class="fa fa-angle-double-right fa-fw fa-lg" aria-hidden="true"></i> {{ ucfirst($app_instance->app_instance) }}</a></li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @else
                                    <li><a href="{{ url('apps/app=' . $app_type) }}"><i class="fa fa-angle-double-right fa-fw fa-lg" aria-hidden="true"></i> {{ $app_instances->first()->displayName() }}</a></li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endif
{{-- Routing --}}
                @if($routing_menu)
                    <li class="dropdown">
                        <a href="{{ url('routing') }}" class="dropdown-toggle" data-hover="dropdown"
                           data-toggle="dropdown"><i class="fa fa-random fa-fw fa-lg fa-nav-icons"
                                                     aria-hidden="true"></i> <span
                                class="tw:md:hidden tw:2xl:inline-block">{{ __('Routing') }}</span></a>
                        <ul class="dropdown-menu">
                        @foreach($routing_menu as $routing_menu_group)
                            @if(!$loop->first)
                                <li role="presentation" class="divider"></li>
                            @endif
                            @foreach($routing_menu_group as $routing_menu_entry)
                                <li><a href="{{ url('routing/protocol=' . $routing_menu_entry['url']) }}"><i class="fa fa-{{ $routing_menu_entry['icon'] }} fa-fw fa-lg" aria-hidden="true"></i> {{ $routing_menu_entry['text'] }}</a></li>
                            @endforeach
                        @endforeach

                        @if($bgp_alerts)
                            <li role="presentation" class="divider"></li>
                                <li><a href="{{ url('routing/protocol=bgp/adminstatus=start/state=down') }}"><i
                                            class="fa fa-exclamation-circle fa-fw fa-lg"
                                            aria-hidden="true"></i> {{ __('Alerted BGP :alert_count', ['alert_count' => $bgp_alerts]) }}
                                    </a></li>
                        @endif
                        @can('peering-db.view')
                            @if($show_peeringdb)
                                <li role="presentation" class="divider"></li>
                                <li><a href="{{ url('peering') }}"><i class="fa fa-hand-o-right fa-fw fa-lg"
                                                                      aria-hidden="true"></i> {{ __('PeeringDB') }}</a>
                                </li>
                            @endif
                        @endcan
                        </ul>
                    </li>
                @endif
{{-- Alerts --}}
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                        <i class="fa fa-exclamation-circle text-{{ $alert_menu_class }} fa-fw fa-lg" aria-hidden="true"></i>
                        <span class="badge badge-navbar-user count-notif badge-{{ $alert_menu_class }}">{{ $alert_count }}</span>
                    <span class="tw:md:hidden tw:2xl:inline-block">{{ __('Alerts') }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ url('alerts') }}"><i class="fa fa-bell fa-fw fa-lg"
                                                             aria-hidden="true"></i> {{ __('Notifications') }}</a></li>
                        @can('viewAny', \App\Models\AlertLog::class)
                        <li><a href="{{ url('alert-log') }}"><i class="fa fa-file-text fa-fw fa-lg"
                                                                aria-hidden="true"></i> {{ __('Alert History') }}</a></li>
                        @endcan
                        <li><a href="{{ url('alert-stats') }}"><i class="fa fa-bar-chart fa-fw fa-lg"
                                                                  aria-hidden="true"></i> {{ __('Statistics') }}</a></li>
                        @if($show_alert_divider)
                        <li role="presentation" class="divider"></li>
                        @endif
                        @can('viewAny', \App\Models\AlertRule::class)
                        <li><a href="{{ url('alert-rules') }}"><i class="fa fa-list fa-fw fa-lg"
                                                                  aria-hidden="true"></i> {{ __('Alert Rules') }}</a></li>
                        @endcan
                        @can('viewAny', \App\Models\AlertOperation::class)
                        <li><a href="{{ route('alert-operations.index') }}"><i class="fa fa-sliders fa-fw fa-lg"
                                                                       aria-hidden="true"></i> {{ __('Operations') }}</a></li>
                        @endcan
                        @can('viewAny', \App\Models\AlertSchedule::class)
                        <li><a href="{{ url('alert-schedule') }}"><i class="fa fa-calendar fa-fw fa-lg"
                                                                     aria-hidden="true"></i> {{ __('Scheduled Maintenance') }}
                            </a></li>
                        @endcan
                        @can('viewAny', \App\Models\AlertTemplate::class)
                        <li><a href="{{ url('templates') }}"><i class="fa fa-file fa-fw fa-lg"
                                                                aria-hidden="true"></i> {{ __('Alert Templates') }}</a>
                        </li>
                        @endcan
                        @can('viewAny', \App\Models\AlertTransport::class)
                        <li><a href="{{ url('alert-transports') }}"><i class="fa fa-bus fa-fw fa-lg"
                                                                       aria-hidden="true"></i> {{ __('Alert Transports') }}
                            </a></li>
                        @endcan
                    </ul>
                </li>
                @includeIf('menu.custom')
            </ul>

            <div class="navbar-form navbar-right global-search tw:relative" x-data="globalSearch()"
                 @keydown.escape="close()" @click.outside="close()">
                <div class="form-group">
                    <input class="form-control" type="search" id="gsearch" name="gsearch" autocomplete="off"
                           placeholder="{{ __('Type / to search') }}"
                           x-model="query" x-ref="input"
                           @input.debounce.250ms="run()" @focus="open = flat.length > 0"
                           @keydown="onKey($event)">
                </div>
                <div x-show="open" x-cloak
                     class="global-search-dropdown tw:absolute tw:right-0 tw:mt-1 tw:w-[50rem] tw:max-w-[90vw] tw:max-h-[70vh] tw:overflow-y-auto tw:bg-white tw:dark:bg-dark-gray-400 tw:border tw:border-gray-200 tw:dark:border-dark-gray-200 tw:rounded-lg tw:shadow-xl tw:z-50">
                    <div x-show="loading && flat.length === 0" class="tw:px-4 tw:py-3 tw:text-gray-500 tw:dark:text-dark-white-400">
                        <i class="fa fa-spinner fa-spin"></i> {{ __('Searching...') }}
                    </div>
                    <div x-show="!loading && flat.length === 0" class="tw:px-4 tw:py-3 tw:text-gray-500 tw:dark:text-dark-white-400">
                        {{ __('No results') }}
                    </div>
                    <template x-for="group in groups" :key="group.type">
                        <div>
                            <div class="tw:px-4 tw:py-1.5 tw:bg-gray-100 tw:dark:bg-dark-gray-200 tw:text-gray-600 tw:dark:text-dark-white-300 tw:text-xs tw:font-bold tw:uppercase" x-text="group.label"></div>
                            <template x-for="item in group.results" :key="group.type + item.url">
                                <a :href="item.url" @mouseenter="active = item.url"
                                   class="tw:flex tw:items-center tw:gap-2.5 tw:px-4 tw:py-2 tw:no-underline tw:text-gray-800 tw:dark:text-dark-white-100 tw:hover:bg-gray-50 tw:dark:hover:bg-dark-gray-300"
                                   :class="(active === item.url ? 'tw:bg-gray-100 tw:dark:bg-dark-gray-300 ' : '') + (item.status ? 'tw:border-l-5 ' + item.status : '')">
                                    <template x-if="item.image">
                                        <img :src="item.image" class="tw:h-7 tw:w-7 tw:shrink-0 tw:object-contain tw:dark:bg-gray-50 tw:dark:rounded tw:dark:p-0.5">
                                    </template>
                                    <template x-if="!item.image">
                                        <i class="fa fa-fw fa-lg tw:shrink-0 icon-theme" :class="item.icon"></i>
                                    </template>
                                    <span class="tw:min-w-0 tw:flex-1">
                                        <span class="tw:block tw:truncate" x-text="item.name"></span>
                                        <span class="tw:block tw:truncate tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400" x-text="item.subtitle"></span>
                                    </span>
                                </a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                        <i class="fa fa-user fa-fw fa-lg fa-nav-icons" aria-hidden="true"></i>
                        <span class="badge badge-navbar-user count-notif {{ $notification_count ? 'badge-danger' : 'badge-default' }}">{{ $notification_count ?: '' }}</span>
                        <span class="tw:md:hidden tw:2xl:inline-block"><small>{{ Auth::user()->username }}</small></span>
                        <span class="visible-xs-inline-block">{{ __('User') }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="{{ url('notifications') }}"><span
                                    class="badge count-notif">{{ $notification_count }}</span> {{ __('Notifications') }}
                            </a></li>
                        <li><a href="{{ route('preferences.index') }}"><i class="fa fa-cog fa-fw fa-lg"
                                                                  aria-hidden="true"></i> {{ __('My Settings') }}</a></li>
                        <li><x-theme-toggle /></li>
                        <li>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa fa-sign-out fa-fw fa-lg" aria-hidden="true"></i> {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}
                            </form>
                        </li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"
                       style="margin-left:5px"><i class="fa fa-cog fa-fw fa-lg fa-nav-icons" aria-hidden="true"></i>
                        <span class="visible-xs-inline-block">{{ __('settings.title') }}</span></a>
                    <ul class="dropdown-menu">
                        @canany(['settings.view', 'settings.update'])
                        <li><a href="{{ url('settings') }}"><i class="fa fa-cogs fa-fw fa-lg"
                                                               aria-hidden="true"></i> {{ __('Global Settings') }}</a></li>
                        <li><a href="{{ url('validate') }}"><i class="fa fa-check-circle fa-fw fa-lg"
                                                               aria-hidden="true"></i> {{ __('Validate Config') }}</a></li>
                        @endcanany
                        @can('viewAny', \App\Models\User::class)
                        <li role="presentation" class="divider"></li>
                        <li><a href="{{ route('users.index') }}"><i class="fa fa-user-circle-o fa-fw fa-lg"
                                                                    aria-hidden="true"></i> {{ __('Manage Users') }}</a>
                        </li>
                        @endcan
                        @can('auth-log.view')
                        <li><a href="{{ route('auth-log') }}"><i class="fa fa-shield fa-fw fa-lg"
                                                              aria-hidden="true"></i> {{ __('Auth History') }}</a></li>
                        @endcan
                        @if(Gate::allows('viewAny', \App\Models\PollerCluster::class) || Gate::allows('viewAny', \App\Models\PollerGroup::class))
                        <li role="presentation" class="divider"></li>
                        <li class="dropdown-submenu">
                            <a href="{{ route('poller.index') }}"><i class="fa fa-th-large fa-fw fa-lg" aria-hidden="true"></i> {{ __('Poller') }}</a>
                            <ul class="dropdown-menu">
                                @can('viewAny', \App\Models\PollerCluster::class)
                                <li><a href="{{ route('poller.index') }}"><i class="fa fa-th-large fa-fw fa-lg" aria-hidden="true"></i> {{ __('Poller') }}</a></li>
                                @endcan
                                @can('viewAny', \App\Models\PollerGroup::class)
                                <li><a href="{{ route('poller.groups') }}"><i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> {{ __('Groups') }}</a></li>
                                @endcan
                                @can('poller.update')
                                <li><a href="{{ route('poller.settings') }}"><i class="fa fa-gears fa-fw fa-lg" aria-hidden="true"></i> {{ __('Settings') }}</a></li>
                                @endcan
                                @can('viewAny', \App\Models\PollerCluster::class)
                                <li><a href="{{ route('poller.performance') }}"><i class="fa fa-line-chart fa-fw fa-lg" aria-hidden="true"></i> {{ __('Performance') }}</a></li>
                                <li><a href="{{ route('poller.log') }}"><i class="fa fa-file-text fa-fw fa-lg" aria-hidden="true"></i> {{ __('Log') }}</a></li>
                                @endcan
                            </ul>
                        </li>
                        @endif
                        @can('api.access')
                        <li role="presentation" class="divider"></li>
                        <li class="dropdown-submenu">
                            <a href="#"><i class="fa fa-code fa-fw fa-lg" aria-hidden="true"></i> {{ __('API') }}</a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ route('api-access.index') }}"><i class="fa fa-cog fa-fw fa-lg"
                                                                         aria-hidden="true"></i> {{ __('API Tokens') }}
                                    </a></li>
                                <li><a href="https://docs.librenms.org/API/" target="_blank" rel="noopener"><i
                                            class="fa fa-book fa-fw fa-lg" aria-hidden="true"></i> {{ __('API Docs') }}</a>
                                </li>
                            </ul>
                        </li>
                        @endcan
                        <li role="presentation" class="divider"></li>
                        <li class="dropdown-submenu" id="countdown_timer_menu" style="display: none">
                            <a href="#"><i class="fa fa-clock-o fa-fw fa-lg"></i> <span id="countdown_timer"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#" id="countdown_timer_pause"><i class="fa fa-pause fa-fw fa-lg"></i> {{ __('Pause') }}</a></li>
                                <li><a href="#" id="countdown_timer_refresh"><i class="fa fa-arrows-rotate fa-fw fa-lg"></i> {{ __('Refresh') }}</a></li>
                            </ul>
                        </li>
                        <li role="presentation" class="divider" id="countdown_timer_divider" style="display: none"></li>
                        <li><a href="{{ url('about') }}"><i class="fa-solid fa-circle-info fa-fw fa-lg"
                                                            aria-hidden="true"></i> {{ __('About :project_name', ['project_name' => $project_name]) }}
                            </a></li>
                    </ul>
                </li>
            </ul>
        </div>
</nav>

<style>
    @media (max-width: 767px) {
        /* Make the header a flex container to place search between logo and toggle button */
        .navbar-header {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            height: 50px;
            padding-left: 15px;
            padding-right: 15px;
            float: none !important;
        }

        .navbar-brand {
            flex-shrink: 0 !important;
            float: none !important;
            height: 50px !important;
            padding: 10px 0 !important;
            margin: 0 !important;
            display: flex;
            align-items: center;
        }

        .navbar-toggle {
            flex-shrink: 0 !important;
            float: none !important;
            margin: 0 !important;
            padding: 9px 10px !important;
        }

        /* Mobile Search Bar styling */
        .navbar-header .global-search {
            display: block !important;
            flex: 1 1 auto;
            margin: 0 10px !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }

        .navbar-header .global-search .form-group {
            margin: 0 !important;
            width: 100%;
        }

        .navbar-header .global-search input {
            width: 100% !important;
            height: 34px;
            padding: 6px 12px;
            border-radius: 4px;
        }

        /* Center and format the search results dropdown on mobile */
        .navbar-header .global-search .global-search-dropdown {
            left: 50% !important;
            right: auto !important;
            transform: translateX(-50%) !important;
            width: 95vw !important;
            max-width: 95vw !important;
        }
    }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('globalSearch', () => ({
            query: '',
            groups: [],
            flat: [],
            open: false,
            loading: false,
            active: '',
            seq: 0,
            controllers: [],
            endpoints: @js([
                route('ajax.search.devices'),
                route('ajax.search.ports'),
                route('ajax.search.health'),
                route('ajax.search.routing'),
                route('ajax.search.logs'),
            ]),
            order: ['devices', 'ports', 'sensors', 'wireless', 'storage', 'mempools', 'processors', 'bgp', 'eventlog'],
            run() {
                let q = this.query.trim();
                if (q === '') { this.reset(); return; }
                this.open = true;
                this.loading = true;
                this.active = '';
                this.groups = [];
                this.flat = [];
                this.controllers.forEach(c => c.abort());
                this.controllers = [];
                let seq = ++this.seq;
                let collected = {};
                let pending = this.endpoints.length;
                this.endpoints.forEach(url => {
                    let controller = new AbortController();
                    this.controllers.push(controller);
                    fetch(url + '?search=' + encodeURIComponent(q), {
                        signal: controller.signal,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (seq !== this.seq) { return; }
                            (data.groups || []).forEach(g => { collected[g.type] = g; });
                            this.groups = this.order.filter(t => collected[t]).map(t => collected[t]);
                            this.flat = this.groups.flatMap(g => g.results);
                        })
                        .catch(() => {})
                        .finally(() => { pending--; if (seq === this.seq && pending === 0) { this.loading = false; } });
                });
            },
            onKey(e) {
                if (e.key === 'ArrowDown') { e.preventDefault(); this.move(1); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); this.move(-1); }
                else if (e.key === 'Enter') { e.preventDefault(); this.go(); }
            },
            move(dir) {
                if (this.flat.length === 0) { return; }
                this.open = true;
                let i = this.flat.findIndex(it => it.url === this.active);
                i = (i + dir + this.flat.length) % this.flat.length;
                this.active = this.flat[i].url;
            },
            go() {
                let url = this.active || (this.flat[0] && this.flat[0].url);
                if (url) { window.location.href = url; }
            },
            close() { this.open = false; },
            reset() { this.controllers.forEach(c => c.abort()); this.controllers = []; this.groups = []; this.flat = []; this.open = false; this.active = ''; this.loading = false; },
        }));
    });

    var hideDashboardEditor = {{ (int) $hide_dashboard_editor }};
    function toggleDashboardEditor() {
        $.ajax({
            url: '{{ route('preferences.store') }}',
            dataType: 'json',
            type: 'POST',
            data: {
                pref: 'hide_dashboard_editor',
                value: hideDashboardEditor ? 0 : 1
            },
            success: function () {
                hideDashboardEditor = hideDashboardEditor ? 0 : 1;
                $('#toggle-dashboard-editor-text').text(hideDashboardEditor ? '{{ __('Show Dashboard Editor') }}' : '{{ __('Hide Dashboard Editor') }}')

                $('#dashboard-editor').collapse(hideDashboardEditor ? 'hide' : 'show');
            }
        });
    }

    @if($browser_push)
        if (localStorage.getItem('notifications') !== 'disabled') {
            Notification.requestPermission().then(function (permission) {
                if (permission === "denied") {
                    localStorage.setItem('notifications', 'disabled');
                }
            });
        }
    @endif

    function repositionSearch() {
        var search = document.querySelector('.global-search');
        if (!search) { return; }
        if (window.innerWidth < 768) {
            var toggle = document.querySelector('.navbar-header .navbar-toggle');
            if (toggle && search.parentElement !== toggle.parentElement) {
                toggle.parentElement.insertBefore(search, toggle);
            }
        } else {
            var rightNav = document.querySelector('#navHeaderCollapse ul.navbar-right');
            if (rightNav && search.parentElement !== rightNav.parentElement) {
                rightNav.parentElement.insertBefore(search, rightNav);
            }
        }
    }

    $(document).ready(function(){
        repositionSearch();
        window.addEventListener('resize', repositionSearch);

        // Focus Global Search when "/" is pressed (unless typing in a field)
        window.addEventListener("keydown", function (e) {
            if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) {
                return;
            }
            var el = document.activeElement;
            if (el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT' || el.isContentEditable)) {
                return;
            }
            e.preventDefault();
            var visibleInput = Array.from(document.querySelectorAll('.global-search input')).find(el => el.offsetWidth > 0 || el.offsetHeight > 0);
            if (visibleInput) {
                visibleInput.focus();
            }
        });
    })

</script>
