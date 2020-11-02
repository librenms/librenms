@extends('device.submenu')

@section('tabcontent')
    <table class="table table-hover table-condensed table-striped">
        <thead>
            <tr>
                <th>@lang('Server Name')</th>
                <th>@lang('Power Status')</th>
                <th>@lang('Operating System')</th>
                <th>@lang('Memory')</th>
                <th>@lang('CPU')</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data['vms'] as $vm)
            <tr>
                <td>
                    @if ($vm->vmDevice)
                        @deviceLink($vm->vmDevice)
                    @else
                        {{ $vm->vmwVmDisplayName }}
                    @endif
                </td>
                <td>
                    <span style="min-width:40px; display:inline-block;" class="label {{ $vm->stateLabel[1] }}">{{ $vm->stateLabel[0] }}</span>
                </td>
                <td>
                    <?php
                    if ($vm->vmwVmGuestOS == 'E: tools not installed') {
                        echo 'Unknown (VMware Tools not installed)';
                    } elseif ($vm->vmwVmGuestOS == '') {
                        echo '<i>(Unknown)</i>';
                    } else {
                        echo \LibreNMS\Util\Rewrite::vmwareGuest($vm->vmwVmGuestOS);
                    }
                    ?>
                </td>

                <td>{{ $vm->memoryFormatted }}</td>
                <td>{{ $vm->vmwVmCpus }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection



