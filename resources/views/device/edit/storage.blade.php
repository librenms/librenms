@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" tab="storage" />

        <div class="table-responsive">
            <table id="storage" class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th>{{ __('Storage') }}</th>
                    <th>{{ __('Size') }}</th>
                    <th>%</th>
                    <th class="col-sm-2">{{ __('% Warn') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($storages as $storage)
                    <tr>
                        <td>{{ $storage->storage_descr }}</td>
                        <td>{{ \LibreNMS\Util\Number::formatBi($storage->storage_size) }}</td>
                        <td>{{ round($storage->storage_perc) }}%</td>
                        <td>
                            <div class="form-group">
                                <input type="number"
                                       min="0"
                                       max="100"
                                       class="form-control input-sm storage-warn"
                                       data-update-url="{{ route('device.edit.storage.update', [$device, $storage]) }}"
                                       value="{{ $storage->storage_perc_warn === null ? '' : round($storage->storage_perc_warn) }}">
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
        function flashStorageResult($input, cssClass) {
            var $group = $input.closest('.form-group');
            $group.addClass(cssClass);
            setTimeout(function () {
                $group.removeClass(cssClass);
            }, 2000);
        }

        $('.storage-warn').on('focusin', function () {
            $(this).data('val', $(this).val());
        });

        $('.storage-warn').on('blur keyup', function (e) {
            if (e.type === 'keyup' && e.keyCode !== 13) return;
            var $this = $(this);
            var data = $this.val();
            if ($this.data('val') === data) return;

            $.ajax({
                type: 'POST',
                url: $this.data('update-url'),
                data: {
                    storage_perc_warn: data,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'ok') {
                        $this.data('val', data);
                        flashStorageResult($this, 'has-success');
                    } else {
                        flashStorageResult($this, 'has-error');
                        toastr.error(response.message);
                    }
                },
                error: function (xhr) {
                    flashStorageResult($this, 'has-error');
                    toastr.error(xhr.responseJSON?.message ?? 'Error updating storage information');
                }
            });
        });
    </script>
@endpush
