@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="processors" />

        <div class="table-responsive">
            <table id="processor" class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>{{ __('Processor') }}</th>
                    <th>%</th>
                    <th class="col-sm-2">{{ __('% Warn') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($processors as $processor)
                    <tr>
                        <td>{{ $processor->processor_descr }}</td>
                        <td>{{ round($processor->processor_usage) }}%</td>
                        <td>
                            <div class="form-group">
                                <input type="number"
                                       min="0"
                                       max="100"
                                       class="form-control input-sm processor-warn"
                                       data-update-url="{{ route('device.edit.processors.update', [$device, $processor]) }}"
                                       value="{{ $processor->processor_perc_warn === null ? '' : round($processor->processor_perc_warn) }}">
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
        function flashProcessorResult($input, cssClass) {
            var $group = $input.closest('.form-group');
            $group.addClass(cssClass);
            setTimeout(function () {
                $group.removeClass(cssClass);
            }, 2000);
        }

        $('.processor-warn').on('focusin', function () {
            $(this).data('val', $(this).val());
        });

        $('.processor-warn').on('blur keyup', function (e) {
            if (e.type === 'keyup' && e.keyCode !== 13) return;
            var $this = $(this);
            var data = $this.val();
            if ($this.data('val') === data) return;

            $.ajax({
                type: 'POST',
                url: $this.data('update-url'),
                data: {
                    processor_perc_warn: data,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'ok') {
                        $this.data('val', data);
                        flashProcessorResult($this, 'has-success');
                    } else {
                        flashProcessorResult($this, 'has-error');
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    flashProcessorResult($this, 'has-error');
                    toastr.error(xhr.responseJSON?.message ?? 'Error updating processor information');
                }
            });
        });
    </script>
@endpush
