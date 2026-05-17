@php
// Wrap $badge in a sensor graph popup, or return $badge unchanged when no sensor is set.
// graph.php reads session('applied_site_style') server-side, so color mode is handled automatically.
$sensorPopup = static fn (array $entry, string $badge): string =>
    ($s = $entry['sensor'] ?? null) instanceof \App\Models\Sensor
        ? \LibreNMS\Util\Url::sensorLink($s, $badge)
        : $badge;
@endphp
<div class="row">
    <div class="col-md-12">
        <x-panel class="device-overview panel-condensed">
            <x-slot name="heading">
                <i class="fa fa-database fa-lg icon-theme" aria-hidden="true"></i>
                <strong><a href="{{ $appLink }}">mdadm RAID</a></strong>
            </x-slot>

            {{-- Arrays summary table --}}
            <x-panel>
                <x-slot name="table">
                    <table class="table table-condensed table-hover tw:mb-0!">
                        <thead>
                            <tr>
                                <th title="Array name">Array</th>
                                <th title="RAID level">Level</th>
                                <th title="Overall array health derived from state flags and device counts.">Health</th>
                                <th title="Current sync operation: idle, check, resync, recover, or repair.">Operation</th>
                                <th title="Number of member devices the array is configured to use (raid_disks).">Disks</th>
                                <th title="Number of devices currently active and contributing to the array.">Active</th>
                                <th title="Number of hot-spare devices ready to replace a failed member.">Spare</th>
                                <th title="Total usable size of the array after RAID overhead." style="white-space:nowrap">Size</th>
                                <th title="Total read errors across all member devices.">Errors</th>
                                <th title="Sectors found inconsistent during last check. Non-zero means parity or mirror data disagrees.">Mismatches</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data->arrayNames() as $arrayName)
                                @php
                                    $arrayData = $data->array($arrayName);
                                    $meta      = $data->arraysMeta[$arrayName] ?? [];
                                    $hEntry    = $arrayData['mdadm_array_health_status'] ?? [];
                                    $opEntry   = $arrayData['mdadm_array_operation_status'] ?? [];
                                    $mmEntry   = $arrayData['mdadm_array_mismatch'] ?? [];

                                    $errors = 0;
                                    foreach (($arrayData['devices'] ?? []) as $dev) {
                                        $errors += $dev['mdadm_device_error']['val'] ?? $dev['mdadm_device_errors']['val'] ?? 0;
                                    }
                                    $errClass = $errors >= 5 ? 'danger' : ($errors >= 1 ? 'warning' : 'default');

                                    $sizeStr = isset($meta['size_bytes']) && $meta['size_bytes'] > 0
                                        ? \LibreNMS\Util\Number::formatBi((int) $meta['size_bytes'])
                                        : '&mdash;';

                                    $arrayLink = \LibreNMS\Util\Url::generate([
                                        'page' => 'device', 'device' => $app->device_id,
                                        'tab'  => 'apps',   'app'    => 'mdadm', 'array' => $arrayName,
                                    ]);

                                    $hBadge  = '<span class="label label-' . e($hEntry['class'] ?? 'default') . '" title="' . e($hEntry['info'] ?? '') . '">' . e($hEntry['label'] ?? 'Unknown') . '</span>';
                                    $opBadge = '<span class="label label-' . e($opEntry['class'] ?? 'default') . '" title="' . e($opEntry['info'] ?? '') . '">' . e($opEntry['label'] ?? 'Unknown') . '</span>';
                                    $mmBadge = '<span class="label label-' . e($mmEntry['class'] ?? 'default') . '">' . e($mmEntry['label'] ?? '0') . '</span>';
                                    $errBadge = '<span class="label label-' . $errClass . '">' . $errors . '</span>';
                                @endphp
                                <tr>
                                    <td><a href="{{ $arrayLink }}">{{ $arrayName }}</a></td>
                                    <td><a href="{{ $arrayLink }}">{{ $meta['raid_level'] ?? '-' }}</a></td>
                                    <td>{!! $sensorPopup($hEntry, $hBadge) !!}</td>
                                    <td>{!! $sensorPopup($opEntry, $opBadge) !!}</td>
                                    <td><a href="{{ $arrayLink }}">{{ $meta['raid_disks'] ?? '-' }}</a></td>
                                    <td><a href="{{ $arrayLink }}">{{ $meta['active_devices'] ?? '-' }}</a></td>
                                    <td><a href="{{ $arrayLink }}">{{ $meta['spare_devices'] ?? '-' }}</a></td>
                                    <td><a href="{{ $arrayLink }}">{!! $sizeStr !!}</a></td>
                                    <td>{!! $errBadge !!}</td>
                                    <td>{!! $sensorPopup($mmEntry, $mmBadge) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-slot>
            </x-panel>

            {{-- Per-array drives inner panels --}}
            @foreach($data->arrayNames() as $arrayName)
                @php
                    $arrayData   = $data->array($arrayName);
                    $hEntry      = $arrayData['mdadm_array_health_status'] ?? [];
                    $metaDevices = $data->arraysDevices[$arrayName] ?? [];

                    $arrayLink = \LibreNMS\Util\Url::generate([
                        'page' => 'device', 'device' => $app->device_id,
                        'tab'  => 'apps',   'app'    => 'mdadm', 'array' => $arrayName,
                    ]);

                    $hBadge = '<span class="label label-' . e($hEntry['class'] ?? 'default') . '" title="' . e($hEntry['info'] ?? '') . '">' . e($hEntry['label'] ?? 'Unknown') . '</span>';
                @endphp
                @if(!empty($metaDevices))
                    <x-panel>
                        <x-slot name="heading">
                            <a href="{{ $arrayLink }}">{{ $arrayName }}</a> Devices
                            <span class="pull-right">{!! $hBadge !!}</span>
                        </x-slot>
                        <x-slot name="table">
                            <table class="table table-condensed table-hover tw:mb-0!">
                                <thead>
                                    <tr>
                                        <th title="Block device path (e.g. /dev/sda).">Path</th>
                                        <th title="Role this device plays in the array.">Role</th>
                                        <th title="Device state flags from the kernel.">Health</th>
                                        <th title="Slot (raid_disk) this device occupies in the array. -1 means spare.">Slot</th>
                                        <th title="Cumulative count of read errors detected on this device.">Errors</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($metaDevices as $devKey => $metaDev)
                                        @php
                                            $sensorDevs = $arrayData['devices'] ?? [];
                                            $dev = is_array($sensorDevs[$devKey] ?? null) ? $sensorDevs[$devKey] : [];

                                            $rawPath = (string) ($metaDev['path'] ?? $metaDev['device_name'] ?? '');
                                            if (str_starts_with($rawPath, '/dev/')) {
                                                $path = $rawPath;
                                            } elseif (str_starts_with($rawPath, 'dev-')) {
                                                $path = '/dev/' . substr($rawPath, 4);
                                            } elseif ($rawPath !== '') {
                                                $path = '/dev/' . ltrim($rawPath, '/');
                                            } else {
                                                $path = $devKey;
                                            }

                                            $dhEntry  = $dev['mdadm_device_health_status'] ?? [];
                                            $errEntry = $dev['mdadm_device_error'] ?? $dev['mdadm_device_errors'] ?? [];

                                            $dhBadge  = '<span class="label label-' . e($dhEntry['class'] ?? 'default') . '" title="' . e($dhEntry['info'] ?? '') . '">' . e($dhEntry['label'] ?? 'Unknown') . '</span>';
                                            $errBadge = '<span class="label label-' . e($errEntry['class'] ?? 'default') . '">' . e($errEntry['label'] ?? '0') . '</span>';
                                        @endphp
                                        <tr>
                                            <td>{{ $path }}</td>
                                            <td>{{ $metaDev['device_role'] ?? '-' }}</td>
                                            <td>{!! $sensorPopup($dhEntry, $dhBadge) !!}</td>
                                            <td>{{ $metaDev['slot'] ?? '-' }}</td>
                                            <td>{!! $sensorPopup($errEntry, $errBadge) !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </x-slot>
                    </x-panel>
                @endif
            @endforeach

        </x-panel>
    </div>
</div>
