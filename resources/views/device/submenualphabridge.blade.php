@extends('device.index')

@section('tab')
    @php
        //   dd($data);
    @endphp

    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .main-wrapper {
            display: flex;
            flex-direction: row-reverse;
            /* right menu on right */
            height: 100vh;
        }

        .right-menu {
            width: 250px;
            background: #343a40;
            color: white;
            padding: 15px;
            overflow-y: auto;
        }

        .right-menu label.menu-label {
            display: block;
            padding: 10px;
            background: #495057;
            margin-bottom: 5px;
            cursor: pointer;
            user-select: none;
            border-radius: 3px;
        }

        .right-menu label.menu-label:hover {
            background: #6c757d;
        }

        .submenu {
            display: none;
            background: #6c757d;
            margin-bottom: 15px;
            border-radius: 3px;
        }

        input[type="checkbox"] {
            display: none;
        }

        input[type="checkbox"]:checked+label+.submenu {
            display: block;
        }

        .submenu label {
            padding-left: 20px;
            font-size: 14px;
            background: #868e96;
            margin: 3px 0;
            border-radius: 3px;
            display: block;
            cursor: pointer;
        }

        .submenu label:hover {
            background: #adb5bd;
        }

        /* Hide radios */
        input[type="radio"] {
            display: none;
        }

        .left-content {
            flex: 1;
            background: #f8f9fa;
            padding: 20px;
            overflow-y: auto;
        }

        .content-block {
            display: none;
        }

        input[type="radio"]:checked+.content-block {
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-wrapper {
                flex-direction: column;
            }

            .right-menu {
                width: 100%;
                order: 2;
            }

            .left-content {
                order: 1;
                padding: 15px;
            }
        }
    </style>

    <div class="main-wrapper">

        <!-- Right Menu -->
        <div class="right-menu">
            <input type="checkbox" id="menu1" />
            <label for="menu1" class="menu-label">Basic Configration</label>
            <div class="submenu">
                <!-- Only labels, no radios here -->
                <label for="submenu1-1">Hostname</label>
                <label for="submenu1-2">Clock Management</label>
            </div>

            <input type="checkbox" id="menu2" />
            <label for="menu2" class="menu-label">Port Configuration</label>
            <div class="submenu">
                <label for="submenu2-1">Port Description</label>
                <label for="submenu2-2">Port Configuration</label>
                <label for="submenu2-3">Rate Limit</label>
                <label for="submenu2-4">Keepalive Detection</label>
                <label for="submenu2-5">Loopback Detection</label>
                <label for="submenu2-6">Port Mirror</label>
                <label for="submenu2-7">Port Filter Configuration</label>
                <label for="submenu2-8">Port Security</label>
                <label for="submenu2-9">Storm Control</label>
                <label for="submenu2-10">Port Protect Group Configuration</label>
                <label for="submenu2-11">Interface Cable Info</label>
            </div>

            <input type="checkbox" id="menu-l2" />
            <label for="menu-l2" class="menu-label">L2 Configuration</label>
            <div class="submenu">
                <label for="submenu-l2-1">GVRP Configuration</label>
                <label for="submenu-l2-2">STP Configuration</label>
                <label for="basic_arp">Basic ARP</label>
                <label for="vlan_config">VLAN Configuration</label>
                <label for="submenu-l2-5">IGMP Snooping</label>
                <label for="submenu-l2-6">LLDP Configuration</label>
                <label for="submenu-l2-7">DDM Configuration</label>
                <label for="submenu-l2-8">Port Channel</label>
                <label for="submenu-l2-9">Ring Protection</label>
                <label for="submenu-l2-10">Multiple Ring Protection</label>
                <label for="submenu-l2-11">BackupLink Configuration</label>
                <label for="submenu-l2-12">DHCP Snooping Configuration</label>
                <label for="submenu-l2-13">Private VLAN Configuration</label>
                <label for="submenu-l2-14">MTU Configuration</label>
                <label for="submenu-l2-15">PDP Configuration</label>
                <label for="submenu-l2-16">IPv6 MLD-Snooping</label>
            </div>


            <input type="checkbox" id="menu-l3" />
            <label for="menu-l3" class="menu-label">L3 Configuration</label>
            <div class="submenu">
                <label for="submenu-l3-1">VLAN Interfaces and IP Addresses</label>
                <label for="submenu-l3-2">DHCP Client Configuration</label>
                <label for="submenu-l3-3">DHCP Server Configuration</label>
                <label for="submenu-l3-4">Static Routing</label>
                <label for="submenu-l3-5">VLAN Interface IPv6 Configuration</label>
                <label for="submenu-l3-6">IPv6 DHCP Client Configuration</label>
                <label for="submenu-l3-7">IPv6 DHCP Server Configuration</label>
                <label for="submenu-l3-8">IPv6 Route Configuration</label>
                <label for="submenu-l3-9">OSPF Route Configuration</label>
                <label for="submenu-l3-10">IGMP Proxy</label>
            </div>

            <input type="checkbox" id="menu-advanced" />
            <label for="menu-advanced" class="menu-label">Advanced Configuration</label>
            <div class="submenu">
                <label for="submenu-adv-1">Qos Configuration</label>
                <label for="submenu-adv-2">Time Range Configuration</label>
                <label for="submenu-adv-3">IP Access List</label>
                <label for="submenu-adv-4">MAC Access List</label>
                <label for="submenu-adv-5">HTTPS Configuration</label>
                <label for="submenu-adv-6">Radius</label>
                <label for="submenu-adv-7">Tacacs</label>
            </div>


            <input type="checkbox" id="menu-network" />
            <label for="menu-network" class="menu-label">Network Management</label>
            <div class="submenu">
                <label for="submenu-net-1">SNMP v1/v2 Community</label>
                <label for="submenu-net-2">Access Management</label>
                <label for="submenu-net-3">SNMPv3 Configuration</label>
                <label for="submenu-net-4">RMON Configuration</label>
            </div>


            <input type="checkbox" id="menu-diagnostic" />
            <label for="menu-diagnostic" class="menu-label">Diagnostic Tool</label>
            <div class="submenu">
                <label for="submenu-diag-1">Ping</label>
                <label for="submenu-diag-2">Tracert</label>
            </div>


            <input type="checkbox" id="menu-system" />
            <label for="menu-system" class="menu-label">System Management</label>
            <div class="submenu">
                <label for="submenu-sys-1">User Management</label>
                <label for="submenu-sys-2">Log Management</label>
                <label for="submenu-sys-3">Startup-config</label>
                <label for="submenu-sys-4">System Software</label>
                <label for="submenu-sys-5">Factory Settings</label>
                <label for="submenu-sys-6">Reboot</label>
            </div>




        </div>

        <!-- Left Content -->
        <div class="left-content">

            <input type="radio" name="menu-l2" id="vlan_config" />
            <div class="content-block">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#vlanConfig" data-toggle="tab" data-url="tabs/vlan-config">VLAN
                            Configuration</a></li>
                    <li><a href="#vlanBatch" data-toggle="tab" data-url="/tabs/vlan-batch">VLAN Batch Configuration</a>
                    </li>
                    <li><a href="#interfaceAttr" data-toggle="tab" data-url="/tabs/interface-vlan-attr">Interface VLAN Attribute Configuration</a>
                    </li>
                    <li><a href="#voiceVlan" data-toggle="tab" data-url="/tabs/voice-vlan">Voice VLAN Configuration</a>
                    </li>
                </ul>
                <!-- Tabs Content -->
                <div class="tab-content" style="margin-top: 20px;">
                    <div class="tab-pane fade in active" id="vlanConfig">
                        <div class="tab-pane fade in active" id="vlanConfig">Loading kk</div>
                    </div>
                    <div class="tab-pane fade" id="vlanBatch">
                        <p>Content for VLAN Batch Configuration.</p>
                    </div>
                    <div class="tab-pane fade" id="interfaceAttr">
                        <p>Content for Interface VLAN Attribute Configuration.</p>
                    </div>
                    <div class="tab-pane fade" id="voiceVlan">
                        <p>Content for Voice VLAN.</p>
                    </div>
                    <div class="tab-pane fade" id="interfaceVoice">
                        <p>Content for Interface Voice VLAN Configuration.</p>
                    </div>
                </div>
            </div>

            <input type="radio" name="menu-l2" id="basic_arp" />
            <div class="content-block">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#basicArp" data-toggle="tab" data-url="/tabs/vlan-config">VLAN
                            Configuration</a></li>
                </ul>
                <!-- Tabs Content -->
                <div class="tab-content" style="margin-top: 20px;">
                    <div class="tab-pane fade in active" id="basicArp">
                        <div class="tab-pane fade in active" id="basicArp">Loading... kkj</div>
                        <div class="tab-pane fade" id="vlanBatch">Loading... kk</div>
                    </div>
                    <div class="tab-pane fade" id="vlanBatch">
                        <p>Content for VLAN Batch Configuration.</p>
                    </div>
                    <div class="tab-pane fade" id="interfaceAttr">
                        <p>Content for Interface VLAN Attribute Configuration.</p>
                    </div>
                    <div class="tab-pane fade" id="voiceVlan">
                        <p>Content for Voice VLAN.</p>
                    </div>
                    <div class="tab-pane fade" id="interfaceVoice">
                        <p>Content for Interface Voice VLAN Configuration.</p>
                    </div>
                </div>
            </div>
        
        </div>

    </div>

     <script>
    function loadTabContent(tabLink) {
        const target = $(tabLink).attr("href"); // #vlanConfig
        const url = $(tabLink).data("url"); // /tabs/vlan-config

        if (!$(target).data("loaded")) {
            $(target).html('Loading...'); // Optional: show loading while fetching
            $.get(url, function(data) {
                $(target).html(data).data("loaded", true);
            });
        }
    }

    // Tab click pe load kara do
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        loadTabContent(e.target);
    });

    // Page load hone pe first active tab ka content load kara do
    $(document).ready(function() {
        const firstActiveTab = $('ul.nav-tabs li.active a[data-toggle="tab"]');
        loadTabContent(firstActiveTab);
    });
</script>

@endsection
