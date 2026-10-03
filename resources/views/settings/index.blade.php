@extends('layouts.librenmsv1')

@section('title', __('settings.title'))

@section('content')
    <div class="container-fluid">
        <div x-data="settingsPage({
                prefix: {{ Js::from(url('settings')) }},
                tab: {{ Js::from($active_tab) }},
                section: {{ Js::from($active_section) }},
                groups: {{ Js::from($groups) }},
                settings: {{ Js::from($settings) }},
             })"
             class="librenms-settings"
        >
            <div class="panel with-nav-tabs panel-default">
                <div class="panel-heading tw:pt-[5px] tw:px-[5px] tw:pb-0">
                    <ul class="nav nav-tabs tw:border-b-0" role="tablist">
                        <template x-for="group in groups" :key="group.name">
                            <li role="presentation" :class="{ 'active': group.name === displayTab }" x-show="groupVisible(group)">
                                <a role="tab" class="tw:cursor-pointer" @click="changeTab(group.name)" x-text="group.text"></a>
                            </li>
                        </template>
                        <li class="pull-right">
                            <form class="form-inline" @submit.prevent>
                                <div class="input-group">
                                    <input id="settings-search" type="search" class="form-control tw:rounded-[4px]!" placeholder="{{ __('Filter Settings') }}" x-model="search">
                                </div>
                            </form>
                        </li>
                    </ul>
                </div>
                <div class="panel-body">
                    <template x-for="group in groups" :key="group.name">
                        <div role="tabpanel" class="tab-pane" x-show="group.name === displayTab">
                            <div class="panel-group" role="tablist">
                                <template x-for="sec in group.sections" :key="sec.name">
                                    <div class="panel panel-default"
                                         x-show="sectionSettings(sec).length"
                                         x-data="{ loaded: false }"
                                         x-effect="if (section === sec.name && group.name === displayTab) loaded = true"
                                    >
                                        <div class="panel-heading" role="tab">
                                            <h4 class="panel-title">
                                                <a role="button" @click="toggleSection(sec.name)">
                                                    <i class="fa fa-chevron-down tw:transition-transform tw:duration-200" :class="{ 'tw:-rotate-90': section !== sec.name }"></i>
                                                    <span x-text="sec.text"></span>
                                                </a>
                                            </h4>
                                        </div>
                                        <div x-show="section === sec.name" x-collapse role="tabpanel">
                                            <div class="panel-body">
                                                <template x-if="loaded">
                                                    <div>
                                                        <template x-if="sec.description">
                                                            <div>
                                                                <h5 x-text="sec.description"></h5>
                                                                <hr />
                                                            </div>
                                                        </template>
                                                        <form class="form-horizontal" @submit.prevent>
                                                            <template x-for="name in sectionSettings(sec)" :key="name">
                                                                <div x-show="settingShown(name)" x-data="librenmsSetting(settings[name])">
                                                                    @include('settings.partials.setting')
                                                                </div>
                                                            </template>
                                                        </form>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        #settings-search::-webkit-search-cancel-button {
            -webkit-appearance: searchfield-cancel-button;
        }
        html:not(.dark) .librenms-settings .with-nav-tabs.panel-default .nav-tabs > li > a {
            color: #777;
        }
        html:not(.dark) .librenms-settings .with-nav-tabs.panel-default .nav-tabs > li > a:hover,
        html:not(.dark) .librenms-settings .with-nav-tabs.panel-default .nav-tabs > li > a:focus {
            background-color: #ddd;
            border-color: transparent;
        }
        html:not(.dark) .librenms-settings .with-nav-tabs.panel-default .nav-tabs > li.active > a,
        html:not(.dark) .librenms-settings .with-nav-tabs.panel-default .nav-tabs > li.active > a:hover,
        html:not(.dark) .librenms-settings .with-nav-tabs.panel-default .nav-tabs > li.active > a:focus {
            color: #555;
            background-color: #fff;
            border-color: #ddd;
            border-bottom-color: transparent;
        }
    </style>
@endpush
