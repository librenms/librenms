@php
    use LibreNMS\Alerting\QueryBuilderParser;
@endphp
<x-panel>
    <x-slot:heading class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2">
        <div>
            @can('create', \App\Models\AlertRule::class)
                <a class="btn btn-primary btn-sm" href="{{ route('alert-rule.create', ['device' => $device?->device_id]) }}">{{ __('Create new alert rule') }}</a>
                <a class="btn btn-default btn-sm" href="{{ route('alert-rule.create', ['device' => $device?->device_id, 'collection' => 1]) }}">{{ __('Create rule from collection') }}</a>
            @endcan
        </div>
        <form method="get" class="form-inline">
            <label class="tw:font-normal">
                {{ __('Results per page') }}
                <select name="per_page" class="form-control input-sm" onchange="this.form.submit()">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" @selected($option == $perPage)>{{ $option }}</option>
                    @endforeach
                </select>
            </label>
        </form>
    </x-slot:heading>
    <x-slot:table>
        <div class="table-responsive">
            <table id="alert-rules-table" class="table table-condensed table-hover table-striped">
                <thead>
                <tr>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Devices') }}</th>
                    <th>{{ __('Transports') }}</th>
                    <th>{{ __('Notification Settings') }}</th>
                    <th>{{ __('Rule') }}</th>
                    <th>{{ __('Severity') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Enabled') }}</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($rules as $rule)
                    @php
                        $status = $statuses[$rule->id] ?? null;
                        $alerting = $status?->alerting;
                        $canUpdate = Gate::allows('update', $rule);
                        [$icon, $color] = $alerting
                            ? (['critical' => ['exclamation', 'danger'], 'warning' => ['warning', 'warning'], 'ok' => ['check', 'success']][$rule->severity] ?? ['info', 'info'])
                            : ($canViewAlerts ? ['check', 'success'] : ['question-circle', 'muted']);
                        $except = $rule->invert_map ? __('EXCEPT') . ' ' : '';
                        $operation = $rule->alertOperation;
                        $segments = $operation?->segments ?? collect();
                        $override = in_array($rule->extra['options']['override_query'] ?? null, ['on', true], true);
                        $muted = ! empty($rule->extra['mute']);
                    @endphp
                    <tr x-data="{
                            enabled: @js(! $rule->disabled),
                            canUpdate: @js($canUpdate),
                            saving: false,
                            async toggle(state) {
                                if (! this.canUpdate || this.saving) return;

                                const previous = this.enabled;
                                this.saving = true;
                                this.enabled = state;
                                try {
                                    await axios.put(@js(route('alert-rule.toggle', $rule->id)), {state});
                                } catch {
                                    this.enabled = previous;
                                    toastr.error(@js(__('This alert rule could not be updated')));
                                } finally {
                                    this.saving = false;
                                }
                            }
                        }" x-bind:class="enabled ? @js($alerting ? $color : '') : 'active'">
                        <td>
                            @if($rule->devices->isNotEmpty() || $rule->groups->isNotEmpty() || $rule->locations->isNotEmpty())
                                <i class="fa fa-fw {{ $rule->groups->isNotEmpty() || $rule->locations->isNotEmpty() ? 'fa-th' : 'fa-server' }} text-primary" title="{{ __('Restricted alert rule') }} #{{ $rule->id }}"></i>
                            @else
                                <i class="fa fa-fw fa-globe text-success" title="{{ __('Global alert rule') }} #{{ $rule->id }}"></i>
                            @endif
                        </td>
                        <td><a href="{{ route('faults', ['filter' => ['rule_id' => ['eq' => $rule->id]]]) }}" title="{{ __('View active alerts for this rule') }}">{{ $rule->name }}</a></td>
                        <td>
                            @foreach($rule->locations->sortBy('location') as $location)
                                <div>{{ $except }}<a href="{{ route('devices', ['filter' => ['location_id' => $location->id]]) }}">{{ $location->location }}</a></div>
                            @endforeach
                            @foreach($rule->groups->sortBy('name') as $group)
                                <div>{{ $except }}<a href="{{ route('device-groups.edit', $group->id) }}">{{ $group->name }}</a></div>
                            @endforeach
                            @foreach($rule->devices->sortBy('hostname') as $mapped)
                                <div>{{ $except }}<a href="{{ route('device.edit', $mapped->device_id) }}">{{ $mapped->display }}</a></div>
                            @endforeach
                            @if($rule->devices->isEmpty() && $rule->groups->isEmpty() && $rule->locations->isEmpty())
                                <a href="{{ route('devices') }}">{{ __('All Devices') }}</a>
                            @endif
                        </td>
                        <td>
                            @forelse($segments->flatMap(fn ($segment) => $segment->transportGroups->map(fn ($group) => ['group', $group->transport_group_id, $group->transport_group_name])->concat($segment->transportSingles->map(fn ($transport) => ['transport', $transport->transport_id, $transport->transport_name]))) as [$type, $id, $name])
                                <div><a href="{{ route('alert-transports.index') }}#{{ $type == 'group' ? 'transport-group' : 'transport' }}-{{ $id }}">{{ $name }}</a></div>
                            @empty
                                {{ __('none') }}
                            @endforelse
                        </td>
                        <td>
                            @if($operation && $segments->isNotEmpty())
                                <small>{{ __('Default step dur.') }}: {{ $operation->default_operation_step_duration_seconds }}s</small><br>
                                <small><strong>{{ $operation->name }}</strong></small>
                                @foreach($segments as $segment)
                                    <br><small>{{ __('steps') }} {{ $segment->escalation_step_from }}–{{ $segment->escalation_step_to ?? '∞' }}, {{ __('start') }} {{ $segment->start_in_seconds }}s, {{ __('step') }} {{ $segment->step_duration_seconds }}s</small>
                                @endforeach
                            @else
                                <small class="text-muted">{{ __('No operation (notifications suppressed)') }}</small>
                            @endif
                        </td>
                        <td class="col-sm-4">
                            @if(($rule->extra['invert'] ?? false) === true)<strong><em>{{ __('Inverted') }}</em></strong>@endif
                            <i>{{ $override ? __('Custom SQL Query') : QueryBuilderParser::fromJson($rule->builder)->toSql(false) }}</i>
                        </td>
                        <td>{{ $rule->severity == 'ok' ? 'OK' : ucwords($rule->severity) }}</td>
                        <td class="tw:whitespace-nowrap">
                            <a href="{{ route('faults', ['filter' => ['rule_id' => ['eq' => $rule->id]]]) }}">
                                <i x-show="enabled" class="fa fa-fw fa-2x fa-{{ $icon }} text-{{ $color }}" title="{{ $alerting ? __('Some devices matching this rule are alerting') : ($canViewAlerts ? $clearStatusTitle : __('Alert status is unavailable')) }}"></i>
                                <i x-show="! enabled" x-cloak class="fa fa-fw fa-2x fa-pause" title="{{ __('Rule is disabled') }}"></i>
                            </a>
                            @if($muted)
                                <i class="fa fa-fw fa-2x fa-volume-off text-primary" title="{{ __('Alerts for this rule are muted') }}"></i>
                            @elseif($operation?->notifications_suppressed)
                                <i class="fa fa-fw fa-2x fa-volume-off text-info" title="{{ __('Operation notifications are suppressed') }}"></i>
                            @endif
                            @if($status?->acknowledged)
                                <i class="fa fa-fw fa-2x fa-sticky-note text-info" title="{{ __('Some alerts for this rule are acknowledged') }}"></i>
                            @endif
                        </td>
                        <td>
                            <x-toggle :checked="! $rule->disabled" x-bind:checked="enabled" x-on:change="toggle($event.target.checked)"
                                      :disabled="! $canUpdate" x-bind:disabled="saving || ! canUpdate" :aria-label="__('Enabled')" />
                        </td>
                        <td class="tw:whitespace-nowrap">
                            @if($canUpdate)
                                <a class="btn btn-primary btn-sm" href="{{ route('alert-rule.edit', ['alert_rule' => $rule, 'device' => $device?->device_id]) }}" title="{{ __('Edit alert rule') }}" aria-label="{{ __('Edit alert rule') }}"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                            @endif
                            @can('delete', $rule)
                                <button type="button" class="btn btn-danger btn-sm" title="{{ __('Delete alert rule') }}"
                                        x-on:click="$dispatch('alert-rule-delete', {id: {{ $rule->id }}, name: @js($rule->name)})"><i class="fa fa-trash"></i></button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-slot:table>
    @if($rules->hasPages())
        <x-slot:footer>
            {{ $rules->links() }}
        </x-slot:footer>
    @endif
</x-panel>

@if($offerDefaults)
    <form method="post" action="{{ route('alert-rules.defaults') }}" class="text-center">
        @csrf
        <button type="submit" class="btn btn-success btn-lg"><i class="fa fa-plus"></i> {{ __('Click here to create the default alert rules!') }}</button>
    </form>
@endif

<div x-data="{ rule: null }" x-on:alert-rule-delete.window="rule = $event.detail">
    <x-modal show="rule" :title="__('Confirm Delete')" maxWidth="md">
        <p>{{ __('If you would like to remove the alert rule') }} <strong x-text="rule?.name"></strong> {{ __('then please click Delete.') }}</p>
        <x-slot:footer>
            <button type="button" class="btn btn-default" x-on:click="rule = null">{{ __('Cancel') }}</button>
            <button type="button" class="btn btn-danger"
                    x-on:click="axios.delete(route('alert-rule.destroy', rule.id)).then(() => window.location.reload()).catch(() => toastr.error(@js(__('Unable to delete alert rule'))))">{{ __('Delete') }}</button>
        </x-slot:footer>
    </x-modal>
</div>
