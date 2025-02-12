@extends('layouts.librenmsv1')

@section('title', __('NAC'))

@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">

    <x-panel title="{{ __('NAC') }}" id="nac-panel">
        <div class="table-responsive">
            <table id="nac-grid" data-toggle="bootgrid" class="table table-hover table-condensed table-striped">
                <thead>
                <tr>
                    <th data-column-id="device_id" data-order="asc" data-width="140px">{{ __('Devices') }}</th>
                    <th data-column-id="port_id" data-width="100px">{{ __('Ports') }}</th>
                    <th data-column-id="mac_address" data-formatter="tooltip" data-width="170px">{{ __('Mac Address') }}</th>
                    <th data-column-id="mac_oui" data-formatter="tooltip" data-sortable="false" data-width="130px">{{ __('Vendor') }}</th>
                    <th data-column-id="ip_address" data-formatter="tooltip" data-width="140px">{{ __('IP Address') }}</th>
                    <th data-column-id="vlan" data-formatter="tooltip" data-width="60px">{{ __('Vlan') }}</th>
                    <th data-column-id="domain" data-formatter="nac_domain" data-formatter="tooltip">{{ __('Domain') }}</th>
                    <th data-column-id="host_mode" data-formatter="nac_mode">{{ __('Host Mode') }}</th>
                    <th data-column-id="username" data-formatter="tooltip" data-width="250px">{{ __('Username') }}</th>
                    <th data-column-id="authz_by" data-formatter="tooltip">{{ __('Auth By') }}</th>
                    <th data-column-id="timeout" data-formatter="time_interval">{{ __('Timeout') }}</th>
                    <th data-column-id="time_elapsed" data-formatter="time_interval" >{{ __('Time Elapsed') }}</th>
                    <th data-column-id="time_left" data-formatter="time_interval" data-visible="false">{{ __('Time Left') }}</th>
                    <th data-column-id="authc_status" data-formatter="nac_authc" data-formatter="tooltip">{{ __('NAC Authc') }}</th>
                    <th data-column-id="authz_status" data-formatter="nac_authz">{{ __('NAC Authz') }}</th>
                    <th data-column-id="method" data-formatter="nac_method">{{ __('NAC Method') }}</th>
                    <th data-column-id="created_at" data-formatter="tooltip">{{ __('First seen') }}</th>
                    <th data-column-id="updated_at" data-formatter="tooltip">{{ __('Last seen') }}</th>
                </tr>
                </thead>
            </table>
        </div>
    </x-panel>

    </div>
    </div>
</div>
@endsection

@section('css')
    <style>
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            nac_grid = $("#nac-grid").bootgrid({
                ajax: true,
                rowCount: [25, 50, 100, -1],
                url: "{{ route('table.port-nac') }}",
                post: function () {
                    var check_showHistorical = document.getElementById('check_showHistorical');
                    if (check_showHistorical) {
                        var showHistorical = check_showHistorical.checked;
                    } else {
                        var showHistorical = false;
                    }

                    return {
                        showHistorical: showHistorical,
                    };
                },
                formatters: {
                    "time_interval": function (column, row) {
                        var value = row[column.id];
                        var res = humanize_duration(value);
                        var res_light = res.split(' ').slice(0, 2).join(' ');
                        return "<span title=\'" + res.trim() + "\' data-toggle=\'tooltip\'>" + res_light + "</span>";
                    },
                    "tooltip": function (column, row) {
                        var value = row[column.id];
                        var vendor = '';
                        if (column.id == 'mac_address' && ((vendor = row['mac_oui']) != '' )) {
                            return "<span title=\'" + value + " (" + vendor + ")\' data-toggle=\'tooltip\'>" + value + "</span>";
                        }
                        return "<span title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
                    },
                    "nac_authz": function (column, row) {
                        var value = row[column.id];
                        if (value === 'authorizationSuccess' || value === 'sussess') {
                            //typo in huawei MIB so we must keep sussess
                            return "<i class=\"fa fa-check-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:green;\"></i>";
                        } else if (value === 'authorizationFailed') {
                            return "<i class=\"fa fa-times-circle fa-lg icon-theme\" aria-hidden=\"true\" style=\"color:red;\"></i>";
                        } else {
                            return "<span class=\'label label-default\' title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
                        }
                    },
                    "nac_domain": function (column, row) {
                        var value = row[column.id];
                        if (value === 'voice') {
                            return "<i class=\"fa fa-phone fa-lg icon-theme\"  aria-hidden=\"true\"></i>";
                        } else if (value === 'data') {
                            return "<i class=\"fa fa-desktop fa-lg icon-theme\"  aria-hidden=\"true\"></i>";
                        } else if (value === 'Disabled') {
                            return "<i class=\"fa fa-desktop fa-lg icon-theme\"  aria-hidden=\"true\"></i>";
                        } else {
                            return "<span class=\'label label-default\' title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
                        }
                    },
                    "nac_authc": function (column, row) {
                        var value = row[column.id];
                        if (value === 'notRun') {
                            return "<span class=\"label label-primary\">notRun</span>";
                        } else if (value === 'running') {
                            return "<span class=\"label label-primary\">running</span>";
                        } else if (value === 'failedOver') {
                            return "<i class=\"fa fa-times-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:red;\"></i>";
                        } else if (value === 'authcSuccess') {
                            return "<i class=\"fa fa-check-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:green;\">";
                        } else if (value === 'authcFailed') {
                            return "<i class=\"fa fa-times-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:red;\"></i>";
                        } else if (value === '6') {
                            return "<i class=\"fa fa-times-circle fa-lg icon-theme\"  aria-hidden=\"true\" style=\"color:red;\"></i>";
                        } else {
                            return "<span class=\'label label-default\' title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
                        }
                    },
                    "nac_method": function (column, row) {
                        var value = row[column.id];
                        if (value === 'dot1x') {
                            return "<span class=\"label label-success\">802.1x</span>";
                        } else if (value === 'macAuthBypass') {
                            return "<span class=\"label label-primary\">MAB</span>";
                        } else if (value === 'other') {
                            return "<span class=\"label label-danger\">Disabled</span>";
                        } else {
                            return "<span class=\'label label-default\' title=\'" + value + "\' data-toggle=\'tooltip\'>" + value + "</span>";
                        }
                    }
                }
            });
            var add = $(".actionBar").append(
                    '<div class="search form-group pull-left" style="width:auto">' +
                    '<?php echo csrf_field() ?>' +
                    '<input type="checkbox" name="check_showHistorical" data-size="small" id="check_showHistorical">' +
                    '&nbsp;Include historical NAC entries' +
                    '</div>');

            $("#check_showHistorical").bootstrapSwitch({
                'onSwitchChange': function(event, state){
                     updateTable();
                }
            });

            function updateTable() {
                $('#nac-grid').bootgrid('reload');
            };
        });
    </script>
@endpush
