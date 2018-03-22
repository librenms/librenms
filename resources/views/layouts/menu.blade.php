<nav class="navbar navbar-default {{ $navbar }} navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navHeaderCollapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="hidden-md hidden-sm navbar-brand" href="">{{ $title_image }}</a>
        </div>

        <div class="collapse navbar-collapse" id="navHeaderCollapse">
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a href="{{ url('overview') }}" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-home fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i> <span class="hidden-sm">Overview</span></a>
                    <ul class="dropdown-menu multi-level" role="menu">
                        <li><a href="{{ url('overview') }}"><i class="fa fa-tv fa-fw fa-lg" aria-hidden="true"></i> Dashboard</a></li>
                        <li class="dropdown-submenu">
                            <a href="{{ url('overview') }}"><i class="fa fa-map fa-fw fa-lg" aria-hidden="true"></i> Maps</a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ url('availability-map') }}"><i class="fa fa-arrow-circle-up fa-fw fa-lg" aria-hidden="true"></i> Availability</a></li>
                                <li><a href="{{ url('map') }}"><i class="fa fa-sitemap fa-fw fa-lg" aria-hidden="true"></i> Network</a></li>
                                @if($device_groups)
                                    <li class="dropdown-submenu"><a href="#"><i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> Device Groups Maps</a><ul class="dropdown-menu scrollable-menu">
                                        @foreach($device_groups as $group)
                                            <li><a href="{{ url('map', [$group->id]) }}" title="{{ $group->desc }}"><i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i>
                                                {{ ucfirst($group->name) }}
                                            </a></li>
                                        @endforeach
                                    </ul></li>
                                @endif
                                <li><a href="{{ url('fullscreenmap') }}"><i class="fa fa-expand fa-fw fa-lg" aria-hidden="true"></i> Geographical</a></li>
                            </ul>
                        </li>
                        <li class="dropdown-submenu">
                            <a><i class="fa fa-plug fa-fw fa-lg" aria-hidden="true"></i> Plugins</a>
                            <ul class="dropdown-menu scrollable-menu">
                                {!! \LibreNMS\Plugins::call('menu') !!}
                                @admin
                                    @if(\LibreNMS\Plugins::count())
                                        <li role="presentation" class="divider"></li>
                                    @endif
                                    <li><a href="{{ url('plugin', ['view' => 'admin']) }}"> <i class="fa fa-lock fa-fw fa-lg" aria-hidden="true"></i>Plugin Admin</a></li>
                                @endadmin
                            </ul>
                        </li>
                        <li class="dropdown-submenu">
                            <a href="{{ url('overview') }}"><i class="fa fa-wrench fa-fw fa-lg" aria-hidden="true"></i> Tools</a>
                            <ul class="dropdown-menu scrollable-menu">
                                <li><a href="{{ url('ripenccapi') }}"><i class="fa fa-star fa-fw fa-lg" aria-hidden="true"></i> RIPE NCC API</a></li>
                                @if(\LibreNMS\Config::get('oxidized.enabled') && Config::has('oxidized.url'))
                                    <li><a href="{{ url('oxidized') }}"><i class="fa fa-stack-overflow fa-fw fa-lg" aria-hidden="true"></i> Oxidized</a></li>
                                @endif
                            </ul>
                        </li>
                        <li role="presentation" class="divider"></li>
                        <li><a href="{{ url('eventlog') }}"><i class="fa fa-bookmark fa-fw fa-lg" aria-hidden="true"></i> Eventlog</a></li>
                        @if(\LibreNMS\Config::get('enable_syslog'))
                            <li><a href="{{ url('syslog') }}"><i class="fa fa-clone fa-fw fa-lg" aria-hidden="true"></i> Syslog</a></li>
                        @endif
                        @if(\LibreNMS\Config::has('graylog.server') && \LibreNMS\Config::has('graylog.port'))
                            <li><a href="{{ url('graylog') }}"><i class="fa fa-clone fa-fw fa-lg" aria-hidden="true"></i> Graylog</a></li>
                        @endif

                        <li><a href="{{ url('inventory') }}"><i class="fa fa-cube fa-fw fa-lg" aria-hidden="true"></i> Inventory</a></li>
                        @if($package_count)
                            <li><a href="{{ url('search/search=packages') }}"><i class="fa fa-archive fa-fw fa-lg" aria-hidden="true"></i> Packages</a></li>
                        @endif

                        <li role="presentation" class="divider"></li>
                        <li><a href="{{ url('search/search=ipv4') }}"><i class="fa fa-search fa-fw fa-lg" aria-hidden="true"></i> IPv4 Address</a></li>
                        <li><a href="{{ url('search/search=ipv6') }}"><i class="fa fa-search fa-fw fa-lg" aria-hidden="true"></i> IPv6 Address</a></li>
                        <li><a href="{{ url('search/search=mac') }}"><i class="fa fa-search fa-fw fa-lg" aria-hidden="true"></i> MAC Address</a></li>
                        <li><a href="{{ url('search/search=arp') }}"><i class="fa fa-search fa-fw fa-lg" aria-hidden="true"></i> ARP Tables</a></li>
                        <li><a href="{{ url('search/search=fdb') }}"><i class="fa fa-search fa-fw fa-lg" aria-hidden="true"></i> FDB Tables</a></li>
                        @if(\LibreNMS\Config::get('poller_modules.mib'))
                            <li role="presentation" class="divider"></li>
                            <li><a href="{{ url('mibs') }}"><i class="fa fa-file-text-o fa-fw fa-lg" aria-hidden="true"></i> MIB definitions</a></li>
                        @endif
                    </ul>
                </li>


                <li class="dropdown">
                    <a href="devices/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-server fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i> <span class="hidden-sm">Devices</span></a>
                    <ul class="dropdown-menu">
                    @if($device_types)
                        <li class="dropdown-submenu">
                            <a href="{{ url('devices') }}"><i class="fa fa-server fa-fw fa-lg" aria-hidden="true"></i> All Devices</a>
                            <ul class="dropdown-menu scrollable-menu">
                            @foreach($device_types as $type)
                                <li><a href="{{ url("devices/type=$type") }}"><i class="fa fa-angle-double-right fa-fw fa-lg" aria-hidden="true"></i> {{ ucfirst($type) }}</a></li>
                            @endforeach
                        </ul></li>
                    @else
                        <li class="dropdown-submenu"><a href="#">No devices</a></li>
                    @endif

                    @if($device_groups)
                        <li class="dropdown-submenu"><a href="#"><i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> Device Groups</a>
                            <ul class="dropdown-menu scrollable-menu">
                            @foreach($device_groups as $group)
                                <li><a href="{{ url("devices/group=$group->id") }}" title="{{ $group->desc }}"><i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> {{ ucfirst($group->name) }}</a></li>
                            @endforeach
                            </ul>
                        </li>
                    @endif

                    @if($locations)
                        <li role="presentation" class="divider"></li>
                        <li class="dropdown-submenu">
                            <a href="#"><i class="fa fa-map-marker fa-fw fa-lg" aria-hidden="true"></i> Geo Locations</a>
                            <ul class="dropdown-menu scrollable-menu">
                            @foreach($locations as $location)
                                    <li><a href="{{ url("devices/location=" . urlencode($location)) }}"><i class="fa fa-building fa-fw fa-lg" aria-hidden="true"></i> {{ $location }}</a></li>
                            @endforeach
                            </ul>
                        </li>
                    @endif
                    @if(auth()->user()->isAdmin())
                        <li role="presentation" class="divider"></li>
                        @if(\LibreNMS\Config::get('poller_modules.mib'))
                            <li><a href="{{ url('mib_assoc') }}"><i class="fa fa-file-text-o fa-fw fa-lg" aria-hidden="true"></i> MIB associations</a></li>
                            <li role="presentation" class="divider"></li>
                        @endif

                        @if(!\LibreNMS\Config::get('navbar.manage_groups.hide', false))
                                <li><a href="{{ url('device-groups') }}"><i class="fa fa-th fa-fw fa-lg" aria-hidden="true"></i> Manage Groups</a></li>
                        @endif
                        <li><a href="{{ url('device-dependencies') }}"><i class="fa fa-group fa-fw fa-lg"></i> Device Dependencies</a></li>
                        <li role="presentation" class="divider"></li>
                        <li><a href="{{ url('addhost') }}"><i class="fa fa-plus fa-fw fa-lg" aria-hidden="true"></i> Add Device</a></li>
                        <li><a href="{{ url('delhost') }}"><i class="fa fa-trash fa-fw fa-lg" aria-hidden="true"></i> Delete Device</a></li>
                    @endif

                    </ul>
                </li>
                @if(\LibreNMS\Config::get('show_services'))
                    <li class="dropdown">
                        <a href="{{ url('services') }}" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-cogs fa-fw fa-lg fa-nav-icons hidden-md" aria-hidden="true"></i> <span class="hidden-sm">Services</span></a>
                        <ul class="dropdown-menu">
                            <li><a href="{{ url('services') }}"><i class="fa fa-cogs fa-fw fa-lg" aria-hidden="true"></i> All Services </a></li>
                            @if($service_status)
                                <li role="presentation" class="divider"></li>
                                @if($service_warning)
                                    <li><a href="{{ url('services/state=warning') }}"><i class="fa fa-bell fa-col-warning fa-fw fa-lg" aria-hidden="true"></i> Warning ({{ $service_warning }})</a></li>
                                @endif
                                @if($service_critical)
                                    <li><a href="{{ url('services/state=critical') }}"><i class="fa fa-bell fa-col-danger fa-fw fa-lg" aria-hidden="true"></i> Critical ({{ $service_critical }})</a></li>
                                @endif
                            @endif
                            @if(auth()->user()->isAdmin())
                                <li role="presentation" class="divider"></li>
                                <li><a href="{{ url('addsrv') }}"><i class="fa fa-plus fa-fw fa-lg" aria-hidden="true"></i> Add Service</a></li>
                            @endif
                        </ul>
                    </li>
                @endif


            </ul>

        </div>
    </div>
</nav>
