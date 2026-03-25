@extends('layouts.librenmsv1')

@section('title', __('Operations'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <span id="message"></span>
            </div>
        </div>

        @can('create', \App\Models\AlertOperation::class)
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#edit-alert-operation"
                    data-operation_id="">{{ __('Create operation') }}</button>
        @endcan
        <br><br>

        <div class="table-responsive">
            <table class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Default step (s)') }}</th>
                    <th>{{ __('Segments') }}</th>
                    <th>{{ __('Transports') }}</th>
                    <th>{{ __('Used by rules') }}</th>
                    <th style="width:120px;">{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($operations as $op)
                    @php
                        $segLines = [];
                        foreach ($op->segments as $seg) {
                            $to = $seg->escalation_step_to === null ? '∞' : (string) $seg->escalation_step_to;
                            $segLines[] = (int) $seg->escalation_step_from . '–' . e($to)
                                . ' @ ' . (int) $seg->start_in_seconds . 's / ' . (int) $seg->step_duration_seconds . 's';
                        }
                        $segStr = $segLines ? implode('<br />', $segLines) : '<em>' . __('none') . '</em>';

                        $tp = [];
                        $seen = [];
                        foreach ($op->segments as $seg) {
                            foreach ($seg->transportSingles as $t) {
                                $k = 's' . $t->transport_id;
                                if (isset($seen[$k])) {
                                    continue;
                                }
                                $seen[$k] = true;
                                $tp[] = e(ucfirst((string) $t->transport_type) . ': ' . $t->transport_name);
                            }
                            foreach ($seg->transportGroups as $g) {
                                $k = 'g' . $g->transport_group_id;
                                if (isset($seen[$k])) {
                                    continue;
                                }
                                $seen[$k] = true;
                                $tp[] = e(__('Group') . ': ' . $g->transport_group_name);
                            }
                        }
                        $tpStr = $tp ? implode('<br />', $tp) : '<em>' . __('none') . '</em>';
                    @endphp
                    <tr id="alert-operation-{{ (int) $op->id }}">
                        <td>{{ $op->name }}</td>
                        <td><small>{{ $op->default_operation_step_duration_seconds !== null ? (int) $op->default_operation_step_duration_seconds : '—' }}</small></td>
                        <td><small>{!! $segStr !!}</small></td>
                        <td class="col-sm-3"><small>{!! $tpStr !!}</small></td>
                        <td>{{ (int) $op->alert_rules_count }}</td>
                        <td>
                            @can('update', \App\Models\AlertOperation::class)
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#edit-alert-operation" data-operation_id="{{ (int) $op->id }}"><i
                                        class="fa fa-pencil"></i></button>
                            @endcan
                            @can('delete', \App\Models\AlertOperation::class)
                                @php $ruleCount = (int) $op->alert_rules_count; @endphp
                                <button type="button" class="btn btn-danger btn-sm btn-delete-alert-operation"
                                        data-operation_id="{{ (int) $op->id }}"
                                        data-operation_name="{{ $op->name }}"
                                        @if($ruleCount > 0) disabled title="{{ __('Assigned to alert rules') }}" @endif><i
                                        class="fa fa-trash"></i></button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('alert.operations.modal')
@endsection

@section('scripts')
    @include('alert.operations.scripts')
@endsection
