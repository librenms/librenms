<x-panel :title="__('Physical Port Layout')">
    <div class="tw:overflow-x-auto tw:p-3 portui-wrapper">
        @foreach($portui['switches'] as $switch)
            <div class="portui-switch tw:inline-block tw:border-2 tw:border-gray-500 tw:dark:border-dark-gray-300 tw:rounded-lg tw:p-3 tw:m-2 tw:bg-gray-200 tw:dark:bg-dark-gray-90 tw:shadow-md">
                @if(!empty($switch['serial']) || $portui['hasMultipleSwitches'])
                    <div class="tw:text-xs tw:font-bold tw:mb-2 tw:text-gray-700 tw:dark:text-dark-gray-100 tw:tracking-wide">
                        <i class="fa fa-server" aria-hidden="true"></i> {{ __('Switch') }} {{ $switch['id'] }}
                        @if(!empty($switch['serial']))
                            <span class="tw:font-normal tw:text-gray-500 tw:dark:text-dark-gray-300">({{ __('Serial') }}: {{ $switch['serial'] }})</span>
                        @endif
                    </div>
                @endif

                <div class="tw:flex tw:flex-wrap tw:gap-2 tw:items-center">
                    @foreach($switch['modules'] as $module)
                        <div class="portui-module tw:inline-flex tw:border tw:border-gray-400 tw:dark:border-dark-gray-400 tw:rounded tw:p-1.5 tw:bg-gray-100 tw:dark:bg-dark-gray-80">
                            @foreach($module['blocks'] as $block)
                                <div class="portui-block tw:inline-block tw:border tw:border-gray-300 tw:dark:border-dark-gray-500 tw:rounded tw:p-1 tw:mx-1 tw:bg-white tw:dark:bg-dark-gray-70">
                                    <table class="portui-table">
                                        @foreach($block['rows'] as $row)
                                            <tr>
                                                @foreach($row as $cell)
                                                    @if($cell)
                                                        @php
                                                            $cellUrl = $cell['url'] ?? ($cell['port_id'] ? url('/device/' . $device->device_id . '/tab=port/port=' . $cell['port_id']) : null);
                                                            $tooltipHtml = htmlspecialchars(
                                                                '<strong>' . e($cell['ifName']) . '</strong>' .
                                                                ($cell['ifDescr'] !== $cell['ifName'] ? '<br>' . e($cell['ifDescr']) : '') .
                                                                '<br>' . __('Admin') . ': <span class="label label-' . ($cell['adminStatus'] === 'up' ? 'success' : 'default') . '">' . e($cell['adminStatus']) . '</span>' .
                                                                ' ' . __('Oper') . ': <span class="label label-' . ($cell['operStatus'] === 'up' ? 'success' : 'danger') . '">' . e($cell['operStatus']) . '</span>' .
                                                                (!empty($cell['speed']) ? '<br>' . __('Speed') . ': ' . e($cell['speed']) : '') .
                                                                (!empty($cell['vlan']) && $cell['vlan'] !== 'Unknown' ? '<br>' . __('VLAN') . ': ' . e($cell['vlan']) : '')
                                                            );
                                                        @endphp
                                                        <td class="port-cell port-cell-{{ $cell['type'] }} border-{{ $cell['adminStatus'] }} bg-{{ $cell['operStatus'] }}"
                                                            data-toggle="tooltip"
                                                            data-placement="top"
                                                            data-html="true"
                                                            title="{!! $tooltipHtml !!}">
                                                            @if($cellUrl)
                                                                <a href="{{ $cellUrl }}" class="port-link">
                                                                    <span class="port-label">{{ $cell['label'] }}</span>
                                                                </a>
                                                            @else
                                                                <span class="port-label">{{ $cell['label'] }}</span>
                                                            @endif
                                                        </td>
                                                    @else
                                                        <td class="port-cell port-cell-empty"></td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="tw:px-4 tw:py-2 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-300 tw:text-xs tw:text-gray-500 tw:dark:text-dark-gray-300 tw:flex tw:flex-wrap tw:gap-4 tw:items-center">
        <span class="tw:font-semibold">{{ __('Legend') }}:</span>
        <span><span class="tw:inline-block tw:w-3 tw:h-3 tw:bg-green-500 tw:border tw:border-green-700 tw:rounded-xs tw:mr-1"></span> {{ __('Connected / Up') }}</span>
        <span><span class="tw:inline-block tw:w-3 tw:h-3 tw:bg-gray-500 tw:border tw:border-blue-700 tw:rounded-xs tw:mr-1"></span> {{ __('Disconnected / Down') }}</span>
        <span><span class="tw:inline-block tw:w-3 tw:h-3 tw:bg-gray-400 tw:border tw:border-gray-600 tw:rounded-xs tw:mr-1"></span> {{ __('Disabled') }}</span>
        <span class="tw:ml-auto"><span class="tw:font-mono tw:text-xs">CU</span> {{ __('Copper') }} | <span class="tw:font-mono tw:text-xs">SFP</span> 1G/10G | <span class="tw:font-mono tw:text-xs">QSFP</span> 40G/100G</span>
    </div>

    <style>
        .portui-table {
            border-collapse: separate;
            border-spacing: 3px;
        }

        .port-cell {
            position: relative;
            text-align: center;
            vertical-align: middle;
            border: 3px solid #555;
            height: 34px;
            width: 44px;
            border-radius: 3px;
            box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.4), 0 1px 3px rgba(0, 0, 0, 0.2);
            transition: transform 0.1s ease, box-shadow 0.1s ease;
            cursor: pointer;
        }

        .port-cell:hover {
            transform: scale(1.08);
            box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.6), 0 2px 6px rgba(0, 0, 0, 0.4);
            z-index: 5;
        }

        .port-cell-cu {
            width: 44px;
            height: 34px;
        }

        .port-cell-sfp {
            width: 44px;
            height: 34px;
            border-style: double;
        }

        .port-cell-qsfp {
            width: 82px;
            height: 34px;
            border-style: double;
        }

        .port-cell-empty {
            border: 2px dashed transparent;
            background-color: transparent;
            box-shadow: none;
            cursor: default;
        }

        .port-cell.border-up {
            border-color: #2e7d32;
        }

        .port-cell.border-down {
            border-color: #1565c0;
        }

        .port-cell.border-disabled {
            border-color: #757575;
        }

        .port-cell.bg-up {
            background-color: #4caf50;
        }

        .port-cell.bg-down {
            background-color: #616161;
        }

        .port-cell .port-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            text-decoration: none;
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
        }

        .port-cell .port-label {
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            user-select: none;
        }
    </style>
</x-panel>
