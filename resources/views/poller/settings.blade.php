@extends('poller.index')

@section('title', __('Poller Settings'))

@section('content')
    @parent
    <div class="panel panel-default" x-data="{ advanced: false, activePoller: {{ Js::from($poller_cluster->keys()->first()) }} }">
        <div class="panel-heading">
            <h3 class="panel-title tw:flex tw:items-center tw:justify-between">
                {{ __('Poller Settings') }}
                <x-toggle size="sm" model="advanced">{{ __('Advanced') }}</x-toggle>
            </h3>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-3 col-md-2">
                    <ul class="nav nav-pills nav-stacked" role="tablist">
                        @foreach($poller_cluster as $id => $poller)
                            <li role="presentation" :class="{ 'active': activePoller === {{ Js::from($id) }} }">
                                <a role="tab" class="tw:cursor-pointer" @click="activePoller = {{ Js::from($id) }}">{{ $poller->poller_name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-sm-9 col-md-10">
                    @foreach($poller_cluster as $id => $poller)
                        <div role="tabpanel" x-show="activePoller === {{ Js::from($id) }}" @if(! $loop->first) x-cloak @endif>
                            @foreach($settings[$id] ?? [] as $setting)
                                <div class="clearfix tw:mb-[10px]"
                                     x-data="librenmsSetting({{ Js::from($setting) }}, { prefix: 'poller.settings', id: {{ Js::from($poller->id) }} })"
                                     @if($setting['advanced']) x-show="advanced" x-cloak @endif
                                >
                                    @include('settings.partials.setting')
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
