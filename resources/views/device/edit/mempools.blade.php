@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="mempools" />

        <div class="table-responsive">
            <table id="mempool" class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>{{ __('Memory') }}</th>
                    <th>%</th>
                    <th class="col-sm-2">{{ __('% Warn') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($mempools as $mempool)
                    <tr>
                        <td>{{ $mempool->mempool_descr }}</td>
                        <td>{{ round($mempool->mempool_perc) }}%</td>
                        <td>
                            <div class="form-group">
                                <input type="number"
                                       min="0"
                                       max="100"
                                       class="form-control input-sm mempool-warn"
                                       data-update-url="{{ route('device.edit.mempools.update', [$device, $mempool]) }}"
                                       value="{{ $mempool->mempool_perc_warn === null ? '' : round($mempool->mempool_perc_warn) }}">
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        function flashMempoolResult($input, cssClass) {
            var $group = $input.closest('.form-group');
            $group.addClass(cssClass);
            setTimeout(function () {
                $group.removeClass(cssClass);
            }, 2000);
        }

        $('.mempool-warn').on('focusin', function () {
            $(this).data('val', $(this).val());
        });

        $('.mempool-warn').on('blur keyup', function (e) {
            if (e.type === 'keyup' && e.keyCode !== 13) return;
            var $this = $(this);
            var data = $this.val();
            if ($this.data('val') === data) return;

            $.ajax({
                type: 'POST',
                url: $this.data('update-url'),
                data: {
                    mempool_perc_warn: data,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'ok') {
                        $this.data('val', data);
                        flashMempoolResult($this, 'has-success');
                    } else {
                        flashMempoolResult($this, 'has-error');
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    flashMempoolResult($this, 'has-error');
                    toastr.error(xhr.responseJSON?.message ?? 'Error updating memory information');
                }
            });
        });
    </script>
@endpush
