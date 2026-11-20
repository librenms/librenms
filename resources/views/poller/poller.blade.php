@extends('poller.index')

@section('title', __('Pollers'))

@section('content')

@parent

@if( $pollers->isNotEmpty() )
<x-panel title="{{ __('Standard Pollers') }}">
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-hover table-condensed">
            <tr>
                <th>{{ __('Poller Name') }}</th>
                <th>{{ __('Environment') }}</th>
                <th>{{ __('Devices Polled') }}</th>
                <th>{{ __('Total Poll Time') }}</th>
                <th>{{ __('Last Run') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
            @foreach($pollers as $poller)
            <tr class="{{ $poller['row_class'] }}" id="row_{{ $poller['id'] }}">
                <td>{{ $poller['poller_name'] }}</td>
                <td>@include('poller.details', ['details' => $poller->poller_details])</td>
                <td>{{ $poller['devices'] }}</td>
                <td>{{ $poller['time_taken'] }} Seconds</td>
                <td>{{ \LibreNMS\Util\Time::format($poller['last_polled'], 'compact') }}</td>
                <td>
                    @can('delete', $poller)
                    @if($poller['long_not_polled'])
                        <button type='button' class='btn btn-danger btn-sm' aria-label={{ __('Delete') }} data-toggle='modal' data-target='#confirm-delete' data-id='{{ $poller['id'] }}' data-pollertype='delete-poller' name='delete-poller'><i class='fa fa-trash' aria-hidden='true'></i></button>
                    @endif
                    @endcan
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</x-panel>
@endif

@if( $poller_cluster->isNotEmpty() )
<div x-data="{
    statusFilter: 'all',
    groupFilter: 'all',
    search: '',
    expandAll: false,
    expanded: {},
    allPollers: {{ Js::from($poller_cluster_meta) }},
    togglePoller(id) {
        this.expanded[id] = !this.expanded[id];
    },
    isExpanded(id) {
        return this.expandAll || !!this.expanded[id];
    },
    matchesPoller(name, nodeId, rowClass, groupIds) {
        if (this.statusFilter === 'healthy' && rowClass !== 'success') {
            return false;
        }
        if (this.statusFilter === 'unhealthy' && rowClass === 'success') {
            return false;
        }
        if (this.groupFilter !== 'all' && !groupIds.includes(Number(this.groupFilter))) {
            return false;
        }
        if (this.search.trim() === '') {
            return true;
        }
        const q = this.search.trim().toLowerCase();
        return name.toLowerCase().includes(q) || nodeId.toLowerCase().includes(q);
    },
    hasAnyMatches() {
        return this.allPollers.some(p => this.matchesPoller(p.name, p.nodeId, p.rowClass, p.groupIds));
    }
}">
    <x-panel>
        <x-slot name="heading">
            <div class="tw:flex tw:flex-wrap tw:justify-between tw:items-center tw:gap-3">
                <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                    <h3 class="panel-title" style="margin-right: 8px;">
                        <i class="fa fa-server" aria-hidden="true"></i> {{ __('Poller Cluster Health') }}
                    </h3>
                    <button type="button" class="lnms-btn"
                            :class="statusFilter === 'all' ? 'lnms-btn-primary' : 'lnms-btn-default'"
                            @click="statusFilter = 'all'">
                        {{ __('All Nodes') }} <span class="lnms-btn-badge">{{ $poller_cluster->count() }}</span>
                    </button>
                    <button type="button" class="lnms-btn"
                            :class="statusFilter === 'healthy' ? 'lnms-btn-success' : 'lnms-btn-default'"
                            @click="statusFilter = (statusFilter === 'healthy' ? 'all' : 'healthy')">
                        <i class="fa fa-check-circle" aria-hidden="true"></i> {{ __('Healthy') }}
                        <span class="lnms-btn-badge">{{ $healthy_count }}</span>
                    </button>
                    @if($poller_cluster->count() > $healthy_count)
                        <button type="button" class="lnms-btn"
                                :class="statusFilter === 'unhealthy' ? 'lnms-btn-danger' : 'lnms-btn-warning'"
                                @click="statusFilter = (statusFilter === 'unhealthy' ? 'all' : 'unhealthy')">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {{ __('Attention') }}
                            <span class="lnms-btn-badge">{{ $poller_cluster->count() - $healthy_count }}</span>
                        </button>
                    @endif
                </div>

                <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-3">
                    <div>
                        <select class="lnms-input"
                                style="width: auto; min-width: 180px;"
                                x-model="groupFilter"
                                aria-label="{{ __('Filter by poller group') }}">
                            <option value="all">{{ __('All Poller Groups') }}</option>
                            @foreach($cluster_groups as $group)
                                <option value="{{ $group['id'] }}">
                                    #{{ $group['id'] }} {{ $group['group_name'] }} - {{ $group['devices_count'] }} {{ __('Devices') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div style="min-width: 240px;">
                        <input type="text"
                               class="lnms-input"
                               x-model="search"
                               placeholder="{{ __('Search poller name or node ID...') }}"
                               aria-label="{{ __('Search pollers') }}">
                    </div>
                    <x-toggle size="sm" model="expandAll">{{ __('Show All Jobs') }}</x-toggle>
                </div>
            </div>
        </x-slot>

        <x-slot name="table">
            <table class="table table-bordered table-condensed table-hover" style="margin-bottom: 0; width: 100%;">
                <tbody>
                <tr>
                    <th style="width: 28px;"></th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Node ID') }}</th>
                    <th>{{ __('Version') }}</th>
                    <th>{{ __('Environment') }}</th>
                    <th>{{ __('Groups Served') }}</th>
                    <th>{{ __('Last Checkin') }}</th>
                    <th class="text-center">{{ __('Workers') }}</th>
                    <th class="text-center">{{ __('Devices Actioned') }}<br><small class="text-muted">{{ __('Last Interval') }}</small></th>
                    <th class="text-center">{{ __('Devices Pending') }}</th>
                    <th>{{ __('Worker Seconds') }}<br><small class="text-muted">{{ __('Consumed/Maximum') }}</small></th>
                    @if($can_delete_any)
                        <th class="text-center" style="width: 40px;">{{ __('Actions') }}</th>
                    @endif
                </tr>
                </tbody>

                <tbody x-show="!hasAnyMatches()" x-cloak>
                <tr>
                    <td colspan="{{ $can_delete_any ? 12 : 11 }}" class="text-center text-muted" style="padding: 16px;">
                        {{ __('No pollers match your search or filter.') }}
                    </td>
                </tr>
                </tbody>

                <tbody>
                @foreach($poller_cluster as $poller)
                    <tr id="row_{{ $poller->id }}"
                        @class(['danger' => $poller->row_class === 'danger', 'warning' => $poller->row_class === 'warning'])
                        x-show="matchesPoller({{ Js::from($poller->poller_name) }}, {{ Js::from((string) $poller->node_id) }}, {{ Js::from($poller->row_class) }}, {{ Js::from($poller->group_ids) }})"
                        style="cursor: pointer;"
                        @click="if (!$event.target.closest('[data-toggle=modal], a')) togglePoller({{ $poller->id }})">
                        <td class="text-center" style="vertical-align: middle;">
                            <button type="button"
                                    class="btn btn-default btn-xs"
                                    @click.stop="togglePoller({{ $poller->id }})"
                                    :aria-expanded="isExpanded({{ $poller->id }})"
                                    title="{{ __('Toggle job breakdown') }}">
                                <i class="fa" :class="isExpanded({{ $poller->id }}) ? 'fa-chevron-down' : 'fa-chevron-right'" aria-hidden="true"></i>
                            </button>
                        </td>
                        <td style="vertical-align: middle;">
                            <div class="tw:flex tw:items-center tw:gap-2">
                                <i class="fa fa-circle text-{{ $poller->row_class }}" aria-hidden="true"></i>
                                <span class="tw:font-bold">{{ $poller->poller_name }}</span>
                                @if($poller->master)
                                    <span class="label label-primary">master</span>
                                @endif
                            </div>
                        </td>
                        <td style="vertical-align: middle;" @class(['danger' => $poller->node_id == ''])>
                            {{ $poller->node_id }}
                        </td>
                        <td style="vertical-align: middle;">
                            {{ $poller->poller_version }}
                        </td>
                        <td style="vertical-align: middle;">
                            @include('poller.details', ['details' => $poller->poller_details])
                        </td>
                        <td style="vertical-align: middle;">
                            {{ $poller->groups_served }}
                        </td>
                        <td style="vertical-align: middle;">
                            <div>{{ \LibreNMS\Util\Time::format($poller->last_report, 'compact') }}</div>
                            @if($poller->last_report)
                                <small class="text-muted">{{ $poller->last_report->diffForHumans() }}</small>
                            @endif
                        </td>

                        @if($poller->primary_stat)
                            <td class="text-center" style="vertical-align: middle;">
                                {{ $poller->primary_stat->workers }}
                            </td>
                            <td class="text-center" style="vertical-align: middle;">
                                {{ $poller->primary_stat->devices }}
                            </td>
                            <td class="text-center" style="vertical-align: middle;">
                                {{ $poller->primary_stat->depth }}
                            </td>
                            <td style="vertical-align: middle;">
                                <div class="tw:flex tw:justify-between tw:items-center tw:gap-2" style="font-size: 12px; margin-bottom: 3px;">
                                    <span x-show="isExpanded({{ $poller->id }})" x-cloak>{{ $poller->primary_stat->worker_seconds }} / {{ $poller->primary_stat->max_seconds }}</span>
                                    <strong class="tw:ml-auto" @class(['text-danger' => $poller->row_class === 'danger', 'text-warning' => $poller->row_class === 'warning'])>{{ $poller->primary_stat->util_pct }}%</strong>
                                </div>
                                <div class="progress" style="height: 5px; margin-bottom: 0;">
                                    <div class="progress-bar progress-bar-{{ $poller->row_class }}"
                                         role="progressbar"
                                         aria-valuenow="{{ $poller->primary_stat->util_pct }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         style="width: {{ min(100, max(2, $poller->primary_stat->util_pct)) }}%;">
                                    </div>
                                </div>
                            </td>
                        @else
                            <td colspan="4" style="vertical-align: middle;">
                                @if($poller->poller_version == \App\Queue\WorkerNode::VERSION && \App\Queue\QueueStatus::showPages())
                                    <a href="{{ route('poller.queues') }}">{{ __('Queue workers share the queues of their poller groups, see Queues') }}</a>
                                @else
                                    <span class="text-muted">{{ __('No job statistics reported') }}</span>
                                @endif
                            </td>
                        @endif

                        @if($can_delete_any)
                            <td class="text-center" style="vertical-align: middle;">
                                @can('delete', $poller)
                                    @if($poller->long_not_polled)
                                        <button type="button"
                                                class="btn btn-danger btn-xs"
                                                aria-label="{{ __('Delete') }}"
                                                data-toggle="modal"
                                                data-target="#confirm-delete"
                                                data-id="{{ $poller->id }}"
                                                data-pollertype="delete-cluster-poller"
                                                name="delete-cluster-poller">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>
                                    @endif
                                @endcan
                            </td>
                        @endif
                    </tr>

                    @foreach($poller->other_stats as $stat)
                        <tr x-show="isExpanded({{ $poller->id }}) && matchesPoller({{ Js::from($poller->poller_name) }}, {{ Js::from((string) $poller->node_id) }}, {{ Js::from($poller->row_class) }}, {{ Js::from($poller->group_ids) }})"
                            x-cloak>
                            <td colspan="6"></td>
                            <td class="text-right" style="vertical-align: middle;">
                                <span class="text-muted">{{ $stat->poller_type }}</span>
                            </td>
                            <td class="text-center" style="vertical-align: middle;">
                                {{ $stat->workers }}
                            </td>
                            <td class="text-center" style="vertical-align: middle;">
                                {{ $stat->devices }}
                            </td>
                            <td class="text-center" style="vertical-align: middle;">
                                {{ $stat->depth }}
                            </td>
                            <td style="vertical-align: middle;">
                                <div class="tw:flex tw:justify-between tw:items-center tw:gap-2" style="font-size: 11px; margin-bottom: 2px;">
                                    <span>{{ $stat->worker_seconds }} / {{ $stat->max_seconds }}</span>
                                    <span @class(['text-warning tw:font-bold' => $stat->util_pct > 100])>{{ $stat->util_pct }}%</span>
                                </div>
                                <div class="progress" style="height: 4px; margin-bottom: 0;">
                                    <div class="progress-bar {{ $stat->util_pct > 100 ? 'progress-bar-warning' : 'progress-bar-success' }}"
                                         role="progressbar"
                                         style="width: {{ min(100, max(1, $stat->util_pct)) }}%;">
                                    </div>
                                </div>
                            </td>
                            @if($can_delete_any)
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </x-slot>

        <x-slot name="footer">
            <small class="text-muted">
                {{ __('Worker seconds indicates the maximum polling throughput a node can achieve in perfect conditions. If the consumed is close to the maximum, consider adding more threads, or better tuning your groups.') }}<br>
                {{ __('If there are devices pending but consumed worker seconds is low, your hardware is not sufficient for the number of devices and the poller cannot reach maximum throughput.') }}
            </small>
        </x-slot>
    </x-panel>
</div>
@endif

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="{{ __('Delete') }}" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Delete">{{ __('Confirm Delete') }}</h5>
            </div>
            <div class="modal-body">
                <p>{{ __('Please confirm that you would like to delete this poller.') }}</p>
            </div>
            <div class="modal-footer">
                <form role="form" class="remove_token_form">
                    @csrf
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger danger" id="poller-removal"
                            data-target="poller-removal">{{ __('Delete') }}
                    </button>
                    <input type="hidden" name="id" id="id" value="">
                    <input type="hidden" name="pollertype" id="pollertype" value="">
                    <input type="hidden" name="confirm" id="confirm" value="yes">
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $('#confirm-delete').on('show.bs.modal', function (e) {
        id = $(e.relatedTarget).data('id');
        pollertype = $(e.relatedTarget).data('pollertype');
        $("#id").val(id);
        $("#pollertype").val(pollertype);
    });

    $('#poller-removal').on("click", function (e) {
        e.preventDefault();
        var id = $("#id").val();
        var pollertype = $("#pollertype").val();
        var url;
        if (pollertype === 'delete-poller') {
            url = route('poller.destroy', {poller: id});
        } else {
            url = route('poller-cluster.destroy', {poller_cluster: id});
        }
        $.ajax({
            type: 'DELETE',
            url: url,
            success: function (result) {
                if (result.status == 0) {
                    toastr.success(result.message);
                    if (pollertype === 'delete-poller') {
                        $("#row_" + id).remove();
                    } else {
                        location.reload();
                    }
                }
                else {
                    toastr.error(result.message);
                }
                $("#confirm-delete").modal('hide');
            },
            error: function () {
                toastr.error('{{ __('An error occurred deleting this poller.') }}');
                $("#confirm-delete").modal('hide');
            }
        });
    });
</script>
@endsection
