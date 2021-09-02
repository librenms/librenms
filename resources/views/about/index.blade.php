@extends('layouts.librenmsv1')

@section('title', __('About'))

@section('content')
<div class="modal fade" id="git_log" tabindex="-1" role="dialog" aria-labelledby="git_log_label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">@lang('Local git log')</h4>
            </div>
            <div class="modal-body">
                <pre>{!! $git_log !!}</pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@Lang('Close')</button>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">

            <h3>@lang('LibreNMS is an autodiscovering PHP/MySQL-based network monitoring system')</h3>
            <table class='table table-condensed table-hover'>
                <tr>
                    <td><b>@lang('Version')</b></td>
                    <td><a target="_blank" href='https://www.librenms.org/changelog.html'>{{ $version_local }}<span id='version_date' style="display: none;">{{ $git_date }}</span></a></td>
                </tr>
                <tr>
                    <td><b>@lang('Database Schema')</b></td>
                    <td>{{ $db_schema }}</td>
                </tr>
                <tr>
                    <td><b>@lang('Web Server')</b></td>
                    <td>{{ $version_webserver }}</td>
                </tr>
                <tr>
                    <td><b>@lang('PHP')</b></td>
                    <td>{{ $version_php }}</td>
                </tr>
                <tr>
                    <td><b>@lang('Python')</b></td>
                    <td>{{ $version_python }}</td>
                </tr>
                <tr>
                    <td><b>@lang('MySQL')</b></td>
                    <td>{{ $version_mysql }}</td>
                </tr>
                <tr>
                    <td><a href="https://laravel.com/"><b>@lang('Laravel')</b></a></td>
                    <td>{{ $version_laravel }}</td>
                </tr>
                <tr>
                    <td><a href="https://oss.oetiker.ch/rrdtool/"><b>@lang('RRDtool')</b></a></td>
                    <td>{{ $version_rrdtool }}</td>
                </tr>
            </table>

          <h3>@lang('LibreNMS is a community-based project')</h3>
          <p>
            @lang('Please feel free to join us and contribute code, documentation, and bug reports:')
            <br />
            <a target="_blank" href="https://www.librenms.org/">@lang('Web site')</a> |
            <a target="_blank" href="https://docs.librenms.org/">@lang('Docs')</a> |
            <a target="_blank" href="https://github.com/librenms/">@lang('GitHub')</a> |
            <a target="_blank" href="https://community.librenms.org/c/help">@lang('Bug tracker')</a> |
            <a target="_blank" href="https://community.librenms.org">@lang('Community Forum')</a> |
            <a target="_blank" href="https://twitter.com/librenms">@lang('Twitter')</a> |
            <a target="_blank" href="https://www.librenms.org/changelog.html">@lang('Changelog')</a> |
            <a href="#" data-toggle="modal" data-target="#git_log">@lang('Local git log')</a>
          </p>

          <h3>@lang('Contributors')</h3>

          <p>@lang('See the <a href=":url">list of contributors</a> on GitHub.', ['url' => 'https://github.com/librenms/librenms/blob/master/AUTHORS.md'])</p>

          <h3>@lang('Acknowledgements')</h3>

          <b>Bruno Pramont</b> Collectd code.<br />
          <b>Dennis de Houx</b> Application monitors for PowerDNS, Shoutcast, NTPD (Client, Server).<br />
          <b>Erik Bosrup</b> Overlib Library.<br />
          <b>Jonathan De Graeve</b> SNMP code improvements.<br />
          <b>Observium</b> Codebase for fork.<br />

      </div>
      <div class="col-md-6">

        <h3>@lang('Statistics')</h3>

        <table class='table table-condensed'>

            @admin
            <tr>
                <td colspan='4'>
                    <span class='bg-danger'>
                        <label for="callback">@lang('Opt in to send anonymous usage statistics to LibreNMS?')</label><br />
                    </span>
                    <input type="checkbox" id="callback" data-size="normal" name="statistics" @if($callback_status) checked @endif>
                    <br />
                    @lang('Online stats:') <a target="_blank" href='https://stats.librenms.org/'>stats.librenms.org</a>
                </td>
            </tr>

            @isset($callback_uuid)
            <tr>
                <td colspan='4'>
                    <button class='btn btn-danger btn-xs' type='submit' name='clear-stats' id='clear-stats'>@lang('Clear remote stats')</button>
                </td>
            </tr>
            @endisset
            @endadmin

            <tr>
                <td><i class='fa fa-fw fa-server fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Devices')</b></td>
                <td class='text-right'>{{ $stat_devices }}</td>
                <td><i class='fa fa-fw fa-link fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Ports')</b></td>
                <td class='text-right'>{{ $stat_ports }}</td>
            </tr>
            <tr>
                <td><i class='fa fa-fw fa-battery-empty fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('IPv4 Addresses')</b></td>
                <td class='text-right'>{{ $stat_ipv4_addy }}</td>
                <td><i class='fa fa-fw fa-battery-empty fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('IPv4 Networks')</b></td>
                <td class='text-right'>{{ $stat_ipv4_nets }}</td>
            </tr>
            <tr>
                <td><i class='fa fa-fw fa-battery-full fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('IPv6 Addresses')</b></td>
                <td class='text-right'>{{ $stat_ipv6_addy }}</td>
                <td><i class='fa fa-fw fa-battery-full fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('IPv6 Networks')</b></td>
                <td class='text-right'>{{ $stat_ipv6_nets }}</td>
            </tr>
            <tr>
                <td><i class='fa fa-fw fa-cogs fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Services')</b></td>
                <td class='text-right'>{{ $stat_services }}</td>
                <td><i class='fa fa-fw fa-cubes fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Applications')</b></td>
                <td class='text-right'>{{ $stat_apps }}</td>
            </tr>
            <tr>
                <td><i class='fa fa-fw fa-microchip fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Processors')</b></td>
                <td class='text-right'>{{ $stat_processors }}</td>
                <td><i class='fa fa-fw fa-braille fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Memory')</b></td>
                <td class='text-right'>{{ $stat_memory }}</td>
            </tr>
            <tr>
                <td><i class='fa fa-fw fa-database fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Storage')</b></td>
                <td class='text-right'>{{ $stat_storage }}</td>
                <td><i class='fa fa-fw fa-hdd-o fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Disk I/O')</b></td>
                <td class='text-right'>{{ $stat_diskio }}</td>
            </tr>
            <tr>
                <td><i class='fa fa-fw fa-cube fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('HR-MIB')</b></td>
                <td class='text-right'>{{ $stat_hrdev }}</td>
                <td><i class='fa fa-fw fa-cube fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Entity-MIB')</b></td>
                <td class='text-right'>{{ $stat_entphys }}</td>
            </tr>
            <tr>
                <td><i class='fa fa-fw fa-clone fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Syslog Entries')</b></td>
                <td class='text-right'>{{ $stat_syslog }}</td>
                <td><i class='fa fa-fw fa-bookmark fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Eventlog Entries')</b></td>
                <td class='text-right'>{{ $stat_events }}</td>
            </tr>
            <tr>
                <td><i class='fa fa-fw fa-dashboard fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('sensors.title')</b></td>
                <td class='text-right'>{{ $stat_sensors }}</td>
                <td><i class='fa fa-fw fa-wifi fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Wireless Sensors')</b></td>
                <td class='text-right'>{{ $stat_wireless }}</td>
            </tr>
            <tr>
                <td><i class='fa fa-fw fa-print fa-lg icon-theme' aria-hidden='true'></i> <b>@lang('Toner')</b></td>
                <td class='text-right'>{{ $stat_toner }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <h3>@lang('License')</h3>
        <pre>
Copyright (C) 2013-{{ date('Y') }} {{ $project_name }} Contributors
Copyright (C) 2006-2012 Adam Armstrong

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <a target="_blank" href="https://www.gnu.org/licenses/">https://www.gnu.org/licenses/</a>.</pre>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $("[name='statistics']").bootstrapSwitch('offColor','danger','size','mini');
    $('input[name="statistics"]').on('switchChange.bootstrapSwitch',  function(event, state) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "callback-statistics", state: state},
            dataType: "json",
            success: function(data){},
            error:function(){
                return $("#switch-state").bootstrapSwitch("toggle");
            }
        });
    });
    $('#clear-stats').on("click", function(event) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "callback-clear"},
            dataType: "json",
            success: function(data){
                location.reload(true);
            },
            error:function(){}
        });
    });

    var ver_date = $('#version_date');
    if (ver_date.text()) {
        ver_date.text(' - '.concat(moment.unix(ver_date.text()))).show();
    }
</script>
@endsection
