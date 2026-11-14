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
@php
    $totalNodes = $poller_cluster->count();
    $healthyNodes = $poller_cluster->where('row_class', 'success')->count();
    $warningNodes = $poller_cluster->where('row_class', 'warning')->count();
    $dangerNodes = $poller_cluster->where('row_class', 'danger')->count();
    $unhealthyNodes = $warningNodes + $dangerNodes;
    $anyDeletable = $poller_cluster->contains(fn ($p) => (bool) $p->long_not_polled);
    $totalCols = $anyDeletable ? 11 : 10;
    $clusterGroups = $cluster_groups ?? collect();
    $allPollersMeta = $poller_cluster->map(fn ($p) => [
        'name' => (string) $p->poller_name,
        'nodeId' => (string) $p->node_id,
        'rowClass' => (string) $p['row_class'],
    ])->values();
@endphp

<div x-data="{
    statusFilter: 'all',
    search: '',
    expandAll: false,
    expanded: {},
    allPollers: {{ Js::from($allPollersMeta) }},
    togglePoller(id) {
        this.expanded[id] = !this.expanded[id];
    },
    isExpanded(id) {
        return this.expandAll || !!this.expanded[id];
    },
    matchesPoller(name, nodeId, rowClass) {
        if (this.statusFilter === 'healthy' && rowClass !== 'success') {
            return false;
        }
        if (this.statusFilter === 'unhealthy' && rowClass === 'success') {
            return false;
        }
        if (this.search.trim() === '') {
            return true;
        }
        const q = this.search.trim().toLowerCase();
        return name.toLowerCase().includes(q) || nodeId.toLowerCase().includes(q);
    },
    groupHasMatches(groupPollers) {
        return groupPollers.some(p => this.matchesPoller(p.name, p.nodeId, p.rowClass));
    },
    hasAnyMatches() {
        return this.allPollers.some(p => this.matchesPoller(p.name, p.nodeId, p.rowClass));
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
                        {{ __('All Nodes') }} <span class="lnms-btn-badge">{{ $totalNodes }}</span>
                    </button>
                    <button type="button" class="lnms-btn"
                            :class="statusFilter === 'healthy' ? 'lnms-btn-success' : 'lnms-btn-default'"
                            @click="statusFilter = (statusFilter === 'healthy' ? 'all' : 'healthy')">
                        <i class="fa fa-check-circle" aria-hidden="true"></i> {{ __('Healthy') }}
                        <span class="lnms-btn-badge">{{ $healthyNodes }}</span>
                    </button>
                    @if($unhealthyNodes > 0)
                        <button type="button" class="lnms-btn"
                                :class="statusFilter === 'unhealthy' ? 'lnms-btn-danger' : 'lnms-btn-warning'"
                                @click="statusFilter = (statusFilter === 'unhealthy' ? 'all' : 'unhealthy')">
                            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i> {{ __('Attention') }}
                            <span class="lnms-btn-badge">{{ $unhealthyNodes }}</span>
                        </button>
                    @endif
                </div>

                <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-3">
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
                    <th>{{ __('Last Checkin') }}</th>
                    <th class="text-center">{{ __('Workers') }}</th>
                    <th class="text-center">{{ __('Devices Actioned') }}<br><small class="text-muted">{{ __('Last Interval') }}</small></th>
                    <th class="text-center">{{ __('Devices Pending') }}</th>
                    <th>{{ __('Worker Seconds') }}<br><small class="text-muted">{{ __('Consumed/Maximum') }}</small></th>
                    @if($anyDeletable)
                        <th class="text-center" style="width: 40px;">{{ __('Actions') }}</th>
                    @endif
                </tr>
                </tbody>

                <tbody x-show="!hasAnyMatches()" x-cloak>
                <tr>
                    <td colspan="{{ $totalCols }}" class="text-center text-muted" style="padding: 16px;">
                        {{ __('No pollers match your search or filter.') }}
                    </td>
                </tr>
                </tbody>

                @foreach($clusterGroups as $group)
                    @php
                        $grpPollers = $group['pollers'];
                        $grpPollersMeta = $grpPollers->map(fn ($p) => [
                            'name' => (string) $p->poller_name,
                            'nodeId' => (string) $p->node_id,
                            'rowClass' => (string) $p['row_class'],
                        ])->values();
                        $grpWorkers = $grpPollers->sum(fn ($p) => (int) ($p->stats->firstWhere('poller_type', 'poller')?->workers ?? 0));
                        $grpConsumed = $grpPollers->sum(fn ($p) => (float) ($p->stats->firstWhere('poller_type', 'poller')?->worker_seconds ?? 0));
                        $grpMax = $grpPollers->sum(function ($p) {
                            $s = $p->stats->firstWhere('poller_type', 'poller');
                            return $s ? ((int) $s->frequency * (int) $s->workers) : 0;
                        });
                        $grpLoadPct = $grpMax > 0 ? (int) round(($grpConsumed / $grpMax) * 100) : 0;
                        $grpLoadLabelClass = $grpLoadPct > 100 ? 'label-warning' : 'label-default';
                    @endphp

                    <tbody x-show="groupHasMatches({{ Js::from($grpPollersMeta) }})">
                    <tr class="active">
                        <td colspan="{{ $totalCols }}" style="padding: 7px 10px;">
                            <div class="tw:flex tw:flex-wrap tw:justify-between tw:items-center tw:gap-2">
                                <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                                    <span class="label label-default">#{{ $group['id'] }}</span>
                                    <strong style="font-size: 14px;">{{ $group['group_name'] }}</strong>
                                    <a href="{{ route('devices', ['filter' => ['poller_group' => ['eq' => $group['id']]]]) }}"
                                       class="label label-primary"
                                       title="{{ __('View devices in this poller group') }}">
                                        <i class="fa fa-server" aria-hidden="true"></i> {{ $group['devices_count'] }} {{ __('Devices') }}
                                    </a>
                                </div>

                                <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                                    <span class="label label-default" title="{{ __('Total poller workers in this group') }}">
                                        {{ __('Workers') }}: {{ $grpWorkers }}
                                    </span>
                                    <span class="label {{ $grpLoadLabelClass }}"
                                          title="{{ __('Average poller worker load across this group') }}">
                                        {{ __('Group Load') }}: {{ $grpLoadPct }}%
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>

                    @foreach($grpPollers as $poller)
                        @php
                            $primaryStat = $poller->stats->firstWhere('poller_type', 'poller') ?? $poller->stats->first();
                            $otherStats = $primaryStat
                                ? $poller->stats->filter(fn ($s) => $s->id !== $primaryStat->id)->values()
                                : collect();
                            $maxSeconds = $primaryStat ? ((int) $primaryStat->frequency * (int) $primaryStat->workers) : 0;
                            $consumedSeconds = $primaryStat ? (float) $primaryStat->worker_seconds : 0;
                            $utilPct = $maxSeconds > 0 ? (int) round(($consumedSeconds / $maxSeconds) * 100) : 0;
                            $isDown = $poller['row_class'] === 'danger';
                            $isWarning = ! $isDown && ($poller['row_class'] === 'warning' || $utilPct > 100);
                            $dotClass = $isDown ? 'text-danger' : ($isWarning ? 'text-warning' : 'text-success');
                            $barClass = $isDown ? 'progress-bar-danger' : ($isWarning ? 'progress-bar-warning' : 'progress-bar-success');
                            $rowKey = $group['id'] . '_' . $poller->id;
                        @endphp

                        <tr id="row_{{ $poller->id }}"
                            data-poller-row="{{ $poller->id }}"
                            @class(['danger' => $isDown, 'warning' => $isWarning])
                            x-show="matchesPoller({{ Js::from($poller->poller_name) }}, {{ Js::from((string) $poller->node_id) }}, {{ Js::from($poller['row_class']) }})"
                            style="cursor: pointer;"
                            @click="if (!$event.target.closest('[data-toggle=modal], a')) togglePoller('{{ $rowKey }}')">
                            <td class="text-center" style="vertical-align: middle;">
                                <button type="button"
                                        class="btn btn-default btn-xs"
                                        @click.stop="togglePoller('{{ $rowKey }}')"
                                        :aria-expanded="isExpanded('{{ $rowKey }}')"
                                        title="{{ __('Toggle job breakdown') }}">
                                    <i class="fa" :class="isExpanded('{{ $rowKey }}') ? 'fa-chevron-down' : 'fa-chevron-right'" aria-hidden="true"></i>
                                </button>
                            </td>
                            <td style="vertical-align: middle;">
                                <div class="tw:flex tw:items-center tw:gap-2">
                                    <i class="fa fa-circle {{ $dotClass }}" aria-hidden="true"></i>
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
                                <div>{{ \LibreNMS\Util\Time::format($poller->last_report, 'compact') }}</div>
                                @if($poller->last_report)
                                    <small class="text-muted">{{ $poller->last_report->diffForHumans() }}</small>
                                @endif
                            </td>

                            @if($primaryStat)
                                <td class="text-center" style="vertical-align: middle;">
                                    {{ $primaryStat->workers }}
                                </td>
                                <td class="text-center" style="vertical-align: middle;">
                                    {{ $primaryStat->devices }}
                                </td>
                                <td class="text-center" style="vertical-align: middle;">
                                    {{ $primaryStat->depth }}
                                </td>
                                <td style="vertical-align: middle;">
                                    <div class="tw:flex tw:justify-between tw:items-center tw:gap-2" style="font-size: 12px; margin-bottom: 3px;">
                                        <span>{{ $primaryStat->worker_seconds }} / {{ $maxSeconds }}</span>
                                        <strong @class(['text-danger' => $isDown, 'text-warning' => $isWarning])>{{ $utilPct }}%</strong>
                                    </div>
                                    <div class="progress" style="height: 5px; margin-bottom: 0;">
                                        <div class="progress-bar {{ $barClass }}"
                                             role="progressbar"
                                             aria-valuenow="{{ $utilPct }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100"
                                             style="width: {{ min(100, max(2, $utilPct)) }}%;">
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

                            @if($anyDeletable)
                                <td class="text-center" style="vertical-align: middle;">
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
                                </td>
                            @endif
                        </tr>

                        @foreach($otherStats as $stat)
                            @php
                                $statMax = (int) $stat->frequency * (int) $stat->workers;
                                $statPct = $statMax > 0 ? (int) round(((float) $stat->worker_seconds / $statMax) * 100) : 0;
                                $statBarClass = $statPct > 100 ? 'progress-bar-warning' : 'progress-bar-success';
                            @endphp
                            <tr data-poller-row="{{ $poller->id }}"
                                x-show="isExpanded('{{ $rowKey }}') && matchesPoller({{ Js::from($poller->poller_name) }}, {{ Js::from((string) $poller->node_id) }}, {{ Js::from($poller['row_class']) }})"
                                x-cloak>
                                <td colspan="5"></td>
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
                                        <span>{{ $stat->worker_seconds }} / {{ $statMax }}</span>
                                        <span @class(['text-warning tw:font-bold' => $statPct > 100])>{{ $statPct }}%</span>
                                    </div>
                                    <div class="progress" style="height: 4px; margin-bottom: 0;">
                                        <div class="progress-bar {{ $statBarClass }}"
                                             role="progressbar"
                                             style="width: {{ min(100, max(1, $statPct)) }}%;">
                                        </div>
                                    </div>
                                </td>
                                @if($anyDeletable)
                                    <td></td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                @endforeach
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
                        $("[data-poller-row='" + id + "']").remove();
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
