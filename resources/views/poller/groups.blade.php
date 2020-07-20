@extends('poller.index')

@section('title', __('Poller Groups'))

@section('content')

@parent

<br />
<div style="overflow: hidden;">
    <div style="float:left;">
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#poller-groups">@lang('Create new poller group')</button>
    </div>
    <div style="float:right;">
        @lang('Poller Group Type'): <b><a href="/settings/poller/distributed">{{ ucfirst($distributed_poller_type) }}</a></b>
    </div>
</div>
<br /><br />
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover table-condensed">
        <tr>
            <th>@lang('ID')</th>
            <th>@lang('Group Name')</th>
            <th>@lang('Devices')</th>
            <th>@lang('Description')</th>
            @if( $distributed_poller_type == 'dynamic')
            <th>@lang('Pattern')</th>
            <th>@lang('Priority')</th>
            @endif
            <th>@lang('Action')</th>
        </tr>
        <tr id="0">
            <td>0</td>
            <td>General @if($default_group_id == 0) (@lang('default')) @endif</td>
            <td><a href="{{ url('devices/poller_group=0') }}">{{ $ungrouped_count }}</a></td>
            <td></td>
            @if( $distributed_poller_type == 'dynamic')
            <td></td>
            <td>0</td>
            @endif
            <td>
        </tr>
        @foreach ($poller_groups as $group)
        <tr id="{{ $group->id }}">
            <td>{{ $group->id }}</td>
            <td>{{ $group->group_name }}@if($group->id == $default_group_id) (@lang('default')) @endif</td>
            <td><a href="{{ url('devices/poller_group=' . $group->id) }}">{{ $group->devices_count }}</a></td>
            <td>{{ $group->descr }}</td>
            @if($distributed_poller_type == 'dynamic')
            <td>{{ $group->getParser()->toSql(false) }}</td>
            <td>{{ $group->priority }}</td>
            @endif
            <td>
                @if($group->id)
                <button type="button" class="btn btn-success btn-xs" data-group_id="{{ $group->id }}" data-toggle="modal" data-target="#poller-groups">@lang('Edit')</button>
                <button type="button" class="btn btn-danger btn-xs" data-group_id="{{ $group->id }}" data-toggle="modal" data-target="#confirm-delete">@lang('Delete')</button>
                @endif
            </td>
        @endforeach
        </tr>
    </table>
</div>

@if(auth()->user()->isAdmin())
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="Delete" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Delete">@lang('Confirm Delete')</h5>
            </div>
            <div class="modal-body">
                <p>@lang('If you would like to remove the Poller Group then please click Delete.')</p>
            </div>
            <div class="modal-footer">
                <form role="form" class="remove_group_form">
                    @csrf
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn-danger danger" id="group-removal" data-target="group-removal">@lang('Delete')</button>
                    <input type="hidden" name="group_id" id="group_id" value="">
                    <input type="hidden" name="type" id="type" value="poller-group-remove">
                    <input type="hidden" name="confirm" id="confirm" value="yes">
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade bs-example-modal-lg" id="poller-groups" tabindex="-1" role="dialog" aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="Create">@lang('Poller Groups')</h4>
            </div>
            <div class="modal-body">
                <form method="post" role="form" id="poller_groups" class="form-horizontal poller-groups-form">
                @csrf
                <input type="hidden" name="group_id" id="group_id" value="">
                <div class="row">
                    <div class="col-md-12">
                        <span id="response"></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="group_name" class="col-sm-3 control-label">@lang('Group Name'):</label>
                            <div class="col-sm-9">
                                <input type="input" class="form-control" id="group_name" name="group_name" placeholder="Group Name">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="descr" class="col-sm-3 control-label">@lang('Description'):</label>
                            <div class="col-sm-9">
                                <input type="input" class="form-control" id="descr" name="descr" placeholder="Description">
                            </div>
                        </div>
                        @if($distributed_poller_type == 'dynamic')
                        <div class="form-group">
                            <label for="priority" class="col-sm-3 control-label">@lang('Priority'):</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="priority" name="priority" placeholder=0>
                            </div>
                        </div>
                        <div class="form-group @if($errors->has('rules')) has-error @endif">
                            <label for="pattern" class="col-sm-3 control-label">@lang('Define Rules'):</label>
                            <div class="col-sm-9">
                                <div id="builder"></div>
                                <span class="help-block">{{ $errors->first('rules') }}</span>
                            </div>
                        </div>
                        @endif
                        <div class="form-group">
                             <div class="col-sm-offset-3 col-sm-9">
                                 <button type="submit" class="btn btn-primary btn-sm" id="create-group" name="create-group">@lang('Add Poller Group')</button>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</form>
@endif
@endsection

@section('scripts')
@if(auth()->user()->isAdmin())
<script>
$('#confirm-delete').on('show.bs.modal', function(e) {
    group_id = $(e.relatedTarget).data('group_id');
    $("#group_id").val(group_id);
});

$('#group-removal').click('', function(e) {
    e.preventDefault();
    groupId = $("#group_id").val();
    $.ajax({
        type: 'DELETE',
        url: "ajax/pollergroup/" + groupId,
        success: function(msg) {
            toastr.success('@lang('Poller Group deleted')');
            $("#confirm-delete").modal('hide');
            location.reload(1);
        },
        error: function(e) {
            toastr.error('@lang('Failed to delete Poller Group'): ' + e.statusText)
            $("#confirm-delete").modal('hide');
        }
    });
});

$('#poller-groups').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var group_id = button.data('group_id');
    $('#group_id').val(group_id);
    if(group_id != '') {
        $('#group_id').val(group_id);
        $.ajax({
            type: "POST",
            url: "ajax_form.php",
            data: { type: "parse-poller-groups", group_id: group_id},
            dataType: "json",
            success: function(output) {
                $('#group_name').val(output.group_name);
                $('#descr').val(output.descr);
                $('#priority').val(output.priority);
                $('#builder').queryBuilder("setRules", output.rule);
            }
        });
    }
});

$('#create-group').click('', function(e) {
    e.preventDefault();
    var group_name = $("#group_name").val();
    var descr = $("#descr").val();
    var rules = JSON.stringify(builder.queryBuilder('getRules'));
    var priority = $('#priority').val();
    var group_id = $('#group_id').val();
    $.ajax({
        type: "POST",
        url: "ajax_form.php",
        data: { type: "poller-groups", group_name: group_name, descr: descr, rules: rules, priority: priority, group_id: group_id},
        dataType: "html",
        success: function(msg){
            if(msg.indexOf("ERROR:") <= -1) {
                $("#message").html('<div class="alert alert-info">'+msg+'</div>');
                $("#poller-groups").modal('hide');
                setTimeout(function() {
                    location.reload(1);
                }, 1000);
            } else {
                $("#error").html('<div class="alert alert-info">'+msg+'</div>');
            }
        },
        error: function(){
            $("#error").html('<div class="alert alert-info">An error occurred.</div>');
        }
    });
});

@if($distributed_poller_type == 'dynamic')
var builder = $('#builder').on('afterApplyRuleFlags.queryBuilder afterCreateRuleFilters.queryBuilder', function () {
    $("[name$='_filter']").each(function () {
        $(this).select2({
            dropdownAutoWidth: true,
            width: 'auto'
        });
    });
}).on('ruleToSQL.queryBuilder.filter', function (e, rule) {
    if (rule.operator === 'regexp') {
        e.value += ' \'' + rule.value + '\'';
    }
}).queryBuilder({
    plugins: [
        'bt-tooltip-errors'
        // 'not-group'
    ],

    filters: {!! $filters !!},
    operators: [
        'equal', 'not_equal', 'between', 'not_between', 'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with', 'is_empty', 'is_not_empty', 'is_null', 'is_not_null', 'in', 'not_in',
        {type: 'less', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
        {type: 'less_or_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
        {type: 'greater', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
        {type: 'greater_or_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
        {type: 'regex', nb_inputs: 1, multiple: false, apply_to: ['string', 'number']},
        {type: 'not_regex', nb_inputs: 1, multiple: false, apply_to: ['string', 'number']}
    ],
    lang: {
        operators: {
            regexp: 'regex',
            not_regex: 'not regex'
        }
    },
    sqlOperators: {
        regexp: {op: 'REGEXP'},
        not_regexp: {op: 'NOT REGEXP'}
    },
    sqlRuleOperator: {
        'REGEXP': function (v) {
            return {val: v, op: 'regexp'};
        },
        'NOT REGEXP': function (v) {
            return {val: v, op: 'not_regexp'};
        }
    }
});
@endif
@endif
</script>
@endsection
@if($distributed_poller_type == 'dynamic')

@section('javascript')
    <script src="{{ asset('js/sql-parser.min.js') }}"></script>
    <script src="{{ asset('js/query-builder.standalone.min.js') }}"></script>
@endsection
@endif
