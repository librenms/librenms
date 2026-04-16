<script>
$(function () {
    function initAoSegmentTransports($sel) {
        $sel.select2({
            width: '100%',
            placeholder: @json(__('Transport / group')),
            allowClear: true,
            ajax: {
                url: '{{ route('ajax.select.alert-transports-groups') }}',
                delay: 150
            },
            dropdownParent: $('#edit-alert-operation')
        });
    }

    function addAoSegmentRow(seg) {
        seg = seg || {};
        var toVal = seg.escalation_step_to === null || typeof seg.escalation_step_to === 'undefined' ? '' : String(seg.escalation_step_to);
        var $tbody = $('<tbody class="ao-segment-group"></tbody>');
        var $trMain = $('<tr class="ao-segment-main"></tr>');
        $trMain.append('<td><input type="number" class="form-control input-sm ao-from" min="1" value="' + (seg.escalation_step_from || 1) + '"></td>');
        $trMain.append('<td><input type="number" class="form-control input-sm ao-to" min="1" placeholder="∞" value="' + toVal + '"></td>');
        $trMain.append('<td><input type="number" class="form-control input-sm ao-start" min="0" value="' + (seg.start_in_seconds || 0) + '"></td>');
        $trMain.append('<td><input type="number" class="form-control input-sm ao-step" min="0" value="' + (seg.step_duration_seconds || 0) + '"></td>');
        $trMain.append('<td class="text-center"><button type="button" class="btn btn-danger btn-xs btn-ao-remove-segment">&times;</button></td>');
        var $trTrans = $('<tr class="ao-segment-trans"></tr>');
        $trTrans.append(
            '<td colspan="5" style="border-top:none;padding-top:4px;">' +
            '<label class="control-label" style="display:block;margin-bottom:4px;font-weight:600;font-size:12px;">{{ e(__('Transports / groups')) }}</label>' +
            '<select class="form-control input-sm ao-transports" multiple="multiple"></select></td>'
        );
        $tbody.append($trMain, $trTrans);
        $('#ao-segments-table').append($tbody);
        initAoSegmentTransports($tbody.find('.ao-transports'));
        if (seg.transports && seg.transports.length) {
            $.each(seg.transports, function (i, t) {
                $tbody.find('.ao-transports').append(new Option(t.text, t.id, true, true));
            });
            $tbody.find('.ao-transports').trigger('change');
        }
    }

    $('#btn-ao-add-segment').on('click', function () {
        addAoSegmentRow({});
    });

    $('#ao-segments-table').on('click', '.btn-ao-remove-segment', function () {
        $(this).closest('tbody.ao-segment-group').remove();
    });

    $('#edit-alert-operation').on('hidden.bs.modal', function () {
        $('#alert-operation-form-error').hide().text('');
    });

    $('#edit-alert-operation').on('show.bs.modal', function (e) {
        var opId = $(e.relatedTarget).data('operation_id');
        $('#alert-operation-form-error').hide().text('');
        $('#ao_operation_id').val(opId || '');
        $('#ao-segments-table tbody.ao-segment-group').remove();
        if (opId) {
            $.get('{{ url('alert-operation') }}/' + opId, function (res) {
                if (res.status !== 'ok' || !res.operation) {
                    return;
                }
                var o = res.operation;
                $('#ao_name').val(o.name);
                $('#ao_default_step_duration').val(
                    o.default_operation_step_duration_seconds != null ? o.default_operation_step_duration_seconds : ''
                );
                if (o.segments && o.segments.length) {
                    $.each(o.segments, function (i, s) {
                        addAoSegmentRow(s);
                    });
                } else {
                    addAoSegmentRow({});
                }
            });
        } else {
            $('#ao_name').val('');
            addAoSegmentRow({});
        }
    });

    $('#btn-save-alert-operation').on('click', function () {
        $('#alert-operation-form-error').hide().text('');
        var opId = $('#ao_operation_id').val();
        var segments = [];
        var err = null;
        $('#ao-segments-table tbody.ao-segment-group').each(function () {
            var $g = $(this);
            var $m = $g.find('tr.ao-segment-main');
            var toRaw = $m.find('.ao-to').val();
            var tr = $g.find('.ao-transports').val() || [];
            if (!tr.length) {
                err = @json(__('Each segment must have at least one transport or group.'));
                return false;
            }
            segments.push({
                escalation_step_from: parseInt($m.find('.ao-from').val(), 10) || 1,
                escalation_step_to: (toRaw === '' || toRaw === null) ? null : parseInt(toRaw, 10),
                start_in_seconds: parseInt($m.find('.ao-start').val(), 10) || 0,
                step_duration_seconds: parseInt($m.find('.ao-step').val(), 10) || 0,
                transports: tr
            });
        });
        if (err) {
            $('#alert-operation-form-error').text(err).show();
            return;
        }
        if (segments.length === 0) {
            $('#alert-operation-form-error').text(@json(__('Add at least one segment.'))).show();
            return;
        }
        var url = '{{ url('alert-operation') }}';
        var dsRaw = $('#ao_default_step_duration').val();
        var body = {
            name: $('#ao_name').val(),
            default_operation_step_duration_seconds: (dsRaw === '' || dsRaw === null) ? null : parseInt(dsRaw, 10),
            segments: segments,
            _token: '{{ csrf_token() }}'
        };
        if (opId) {
            url += '/' + opId;
            body._method = 'PUT';
        }
        $.ajax({
            type: 'POST',
            url: url,
            data: JSON.stringify(body),
            contentType: 'application/json; charset=UTF-8',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '',
                'Accept': 'application/json'
            },
            success: function (data) {
                if (data.status === 'ok') {
                    toastr.success(data.message);
                    $('#edit-alert-operation').modal('hide');
                    window.location.reload();
                } else {
                    $('#alert-operation-form-error').text(data.message || 'Error').show();
                }
            },
            error: function (xhr) {
                var msg = 'Request failed';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = JSON.stringify(xhr.responseJSON.errors);
                }
                $('#alert-operation-form-error').text(msg).show();
            }
        });
    });
});
</script>
<script>
$(document).on('click', '.btn-delete-alert-operation', function () {
    var id = $(this).data('operation_id');
    var name = $(this).data('operation_name');
    if (!confirm(@json(__('Delete operation')) + ' "' + name + '"?')) {
        return;
    }
    $.ajax({
        type: 'POST',
        url: '{{ url('alert-operation') }}/' + id,
        data: {_token: '{{ csrf_token() }}', _method: 'DELETE'},
        dataType: 'json',
        success: function (data) {
            if (data.status === 'ok') {
                toastr.success(data.message);
                $('#alert-operation-' + id).remove();
            } else {
                toastr.error(data.message || 'Error');
            }
        },
        error: function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Request failed';
            toastr.error(msg);
        }
    });
});
</script>
