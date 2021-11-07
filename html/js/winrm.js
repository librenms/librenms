/*
 * winrm.js
 *
 * WinRM Page Javascript
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2021 Thomas Ford
 * @author     Thomas Ford <thomas.ford@thomasaford.com>
 */

// set CSRF for jquery ajax request
var WinRM = (function () {
    var that = this;

    this.bytesToSize = function (bytes) {
        let sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        if (bytes == 0) return '0 Byte';
        let i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
    }

    this.LoadProcesses = function (baseURL, deviceID, processName = null) {
        $("#winrm").bootgrid({
            ajax: true,
            rowCount: [50, 100, 200, -1],
            url: baseURL + '/ajax/table/winrmprocesses',
            post: function () {
                return {
                    device_id: deviceID,
                    process_name: processName,
                };
            },
            formatters: {
                "process-name": function (column, row) {
                    return '<a href="' + baseURL + '/winrm/processes/' + row.name + '">' + row.name + '</a>';
                },
                "process-ws": function (column, row) {
                    return that.bytesToSize(row.ws);
                },
                "process-sysName": function (column, row) {
                    return '<span><a href="' + baseURL + '/device/' + row.device_id + '">' + row.hostname + '</a></br>' + row.sysName + '</span>';
                },
            }
        });
    }

    this.LoadServices = function (baseURL, deviceID, serviceName = null) {
        $("#winrm").bootgrid({
            ajax: true,
            rowCount: [50, 100, 200, -1],
            url: baseURL + '/ajax/table/winrmservices',
            post: function () {
                return {
                    device_id: deviceID,
                    service_name: serviceName,
                };
            },
            formatters: {
                "svc-status-icon": function (column, row) {
                    let label = 'label-danger';
                    switch(row.status){
                        case  1:
                            label = 'label-info'
                            if(row.alerts == 1){
                                label = 'label-danger';
                            }
                            break;
                        case  4:
                            label = 'label-success' // Running
                            break;
                    }
                    return '<span id="status_icon_' + row.id + '" class="alert-status ' + label + '" style="margin-right:8px;float:left;"></span>';
                },
                "svc-sysName": function (column, row) {
                    return '<span><a href="' + baseURL + '/device/' + row.device_id + '">' + row.hostname + '</a></br>' + row.sysName + '</span>';
                },
                "svc-display": function (column, row) {
                    return '<a href="' + baseURL + '/winrm/services/' + row.display_name + '">' + row.display_name + '</a>';
                },
                "svc-name": function (column, row) {
                    return '<a href="' + baseURL + '/winrm/services/' + row.service_name + '">' + row.service_name + '</a>';
                },
                "svc-status": function (column, row) {
                    let service_status = '';
                    switch(row.status){
                        case  1:
                            service_status = 'Stopped'
                            break;
                        case  4:
                            service_status = 'Running'
                            break;
                    }
                    return '<span id="status_' + row.id + '">' + service_status + '</span>';
                },
                "svc-alert": function (column, row) {
                    let alert_checked = '';
                    if(row.alerts == 1){
                        alert_checked = 'checked';
                    }
                    return '<div id="on-off-checkbox-' + row.id + ' class="btn-group btn-group-sm" role="group">'
                        +'<input id="alert_' + row.id + '" type="checkbox" name="service_status" data-service_id="' + row.id + '" ' + alert_checked + ' data-size="small" data-toggle="modal">'
                        +'</div>';
                },
            }
        }).on("loaded.rs.jquery.bootgrid", function() {
            $("[name='service_status']").bootstrapSwitch('offColor','danger');
            $('input[name="service_status"]').on('switchChange.bootstrapSwitch',  function(event, state) {
                event.preventDefault();
                let service_id = $(this).data("service_id");
                $.ajax({
                    method: 'PATCH',
                    url: baseURL + '/ajax/winrmservices/' + service_id,
                    data: { alerts: Number(state) },
                    success: function(result) {
                        switch(result.status) {
                            case "success":
                                var statusElement = $('#status_icon_'+service_id);
                                statusElement.removeClass('label-success');
                                statusElement.removeClass('label-info');
                                statusElement.removeClass('label-danger');
                                switch($('#status_'+service_id).text()){
                                    case 'Stopped':
                                        if(state) {
                                            statusElement.addClass('label-danger');
                                        }
                                        else {
                                            statusElement.addClass('label-info');
                                        }
                                        break;
                                    case 'Running':
                                        statusElement.addClass('label-success');
                                        break;
                                }
                                break;
                            default:
                                console.log(result);
                                toastr.error("This service could not be updated.");
                                break;
                        }
                    },
                    error: function(result) {
                        console.log(result);
                        toastr.error("This service could not be updated.");
                        $('#alert_'+service_id).bootstrapSwitch('toggleState',true);

                        if(result.responseJSON.errors){
                            for(const message in result.responseJSON.errors) {
                                result.responseJSON.errors[message].forEach(element => {
                                    toastr.error(message + " - " + element);
                                });
                            }
                        }
                    }
                });
            });
        });
    }

    this.LoadSoftware = function (baseURL, deviceID, softwareID = null, softwareVersion = null, softwareVendor = null) {
        $("#winrm").bootgrid({
            ajax: true,
            rowCount: [50, 100, 200, -1],
            url: baseURL + '/ajax/table/winrmsoftware',
            post: function () {
                return {
                    device_id: deviceID,
                    software_id: softwareID,
                    software_version: softwareVersion,
                    software_vendor: softwareVendor,
                };
            },
            formatters: {
                "soft-sysName": function (column, row) {
                    return '<span><a href="' + baseURL + '/device/' + row.device_id + '">' + row.hostname + '</a></br>' + row.sysName + '</span>';
                },
                "soft-name": function (column, row) {
                    return '<a href="' + baseURL + '/winrm/software/' + row.software_id + '">' + row.name + '</a>';
                },
                "soft-vendor": function (column, row) {
                    return '<a href="' + baseURL + '/winrm/software/vend/' + row.vendor + '">' + row.vendor + '</a>';
                },
                "soft-version": function (column, row) {
                    return '<a href="' + baseURL + '/winrm/software/' + row.software_id + '/' + row.version + '">' + row.version + '</a>';
                },
            }
        });
    }
});