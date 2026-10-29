@extends('layouts.librenmsv1')

@section('title', __('first_run.title'))

@section('content')
    <div class="container tw:max-w-3xl!" x-data="firstRun(@js($config))" x-cloak @beforeunload.window="scan.running && $event.preventDefault()">
        <x-panel class="tw:mt-2">
            <x-slot name="heading">
                <div class="tw:flex tw:items-center tw:justify-between tw:gap-4">
                    <h3 class="panel-title">
                        <i class="fa-solid fa-rocket fa-fw fa-lg" aria-hidden="true"></i> {{ __('first_run.panel_title') }}
                    </h3>
                    {{-- the steps that were reached can be opened again --}}
                    <nav aria-label="{{ __('first_run.panel_title') }}">
                        <span class="tw:md:hidden tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400"
                              x-text="@js(__('first_run.step_of')).replace(':step', step + 1).replace(':total', 4)"></span>
                        <ol class="tw:hidden tw:md:flex tw:items-center tw:gap-3 tw:m-0 tw:p-0 tw:list-none">
                            @foreach(['welcome', 'credentials', 'networks', 'scan'] as $index => $name)
                                <li>
                                    <button type="button" class="tw:flex tw:items-center tw:gap-1.5 tw:bg-transparent tw:border-0 tw:p-0 tw:text-sm tw:disabled:cursor-default"
                                            :disabled="! canGoTo({{ $index }})" @click="step = {{ $index }}"
                                            :aria-current="step === {{ $index }} ? 'step' : false">
                                        <span class="tw:flex tw:items-center tw:justify-center tw:w-5 tw:h-5 tw:rounded-full tw:text-xs tw:font-semibold"
                                              :class="step === {{ $index }} ? 'tw:bg-blue-600 tw:text-white' : (reached > {{ $index }} ? 'tw:bg-green-600 tw:text-white' : 'tw:bg-gray-200 tw:text-gray-500 tw:dark:bg-dark-gray-300 tw:dark:text-dark-white-400')">
                                            <i class="fa-solid fa-check tw:text-[0.6rem]" x-show="step !== {{ $index }} && reached > {{ $index }}"></i>
                                            <span x-show="step === {{ $index }} || reached <= {{ $index }}">{{ $index + 1 }}</span>
                                        </span>
                                        <span :class="step === {{ $index }} ? 'tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100' : 'tw:text-gray-500 tw:dark:text-dark-white-400'">{{ __("first_run.steps.$name") }}</span>
                                    </button>
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </div>
            </x-slot>

            <div class="tw:p-2 tw:sm:p-4">
                {{-- Welcome --}}
                <section x-show="step === 0" class="tw:text-center tw:py-6">
                    <x-logo class="tw:mx-auto tw:mb-6 tw:h-14 tw:max-w-full" />
                    <h1 class="tw:text-2xl tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100 tw:mt-0 tw:mb-2 tw:outline-none" x-ref="heading0" tabindex="-1">{{ __('first_run.welcome.heading') }}</h1>
                    <p class="tw:text-gray-600 tw:dark:text-dark-white-300 tw:max-w-md tw:mx-auto tw:mb-0 tw:text-balance">{{ __('first_run.welcome.intro') }}</p>
                </section>

                {{-- Credentials --}}
                <section x-show="step === 1">
                    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100 tw:mt-0 tw:mb-1 tw:outline-none" x-ref="heading1" tabindex="-1">{{ __('first_run.credentials.heading') }}</h2>
                    <p class="tw:text-gray-600 tw:dark:text-dark-white-300 tw:mb-6 tw:text-pretty">
                        {{ __('first_run.credentials.intro') }}<br>
                        {!! str_replace(':link', '<a href="https://docs.librenms.org/Support/SNMP-Configuration-Examples/" target="_blank" rel="noopener" class="tw:underline">' . e(__('first_run.snmp_docs')) . '</a>', e(__('first_run.credentials.docs'))) !!}
                    </p>

                    {{-- communities and SNMPv3 credentials are one list, tried in the order shown --}}
                    <ol class="tw:rounded-lg tw:border tw:p-0 tw:m-0 tw:list-none"
                        :class="errorFor('credentials') ? 'tw:border-red-400' : 'tw:border-gray-200 tw:dark:border-dark-gray-200'"
                        x-show="credentials.length > 0" x-sort="move(credentials, $item, $position)">
                        <template x-for="(credential, index) in credentials" :key="credential.key + sortKey">
                            <li x-sort:item="index" class="tw:flex tw:items-center tw:gap-3 tw:px-3 tw:py-2 tw:border-b tw:last:border-b-0 tw:border-gray-100 tw:dark:border-dark-gray-300"
                                :class="errorsFor('credentials.' + index).length > 0 && 'tw:bg-red-50 tw:dark:bg-red-400/10'">
                                <span x-sort:handle class="tw:flex tw:items-center tw:gap-2 tw:cursor-move tw:text-gray-400 tw:dark:text-dark-white-400" title="{{ __('first_run.credentials.reorder') }}">
                                    <i class="fa-solid fa-grip-vertical" aria-hidden="true"></i>
                                    <span class="tw:w-4 tw:text-right tw:tabular-nums tw:text-sm" x-text="index + 1"></span>
                                </span>
                                <span class="tw:flex-none tw:w-14 tw:text-center tw:text-xs tw:font-semibold tw:rounded tw:py-0.5 tw:bg-blue-50 tw:text-blue-800 tw:dark:bg-dark-gray-300 tw:dark:text-dark-white-100"
                                      x-text="credential.type === 'v3' ? 'v3' : 'v1/v2c'"></span>
                                <span class="tw:min-w-0 tw:flex-1">
                                    <span class="tw:font-medium tw:text-gray-900 tw:dark:text-dark-white-100" x-text="credentialName(credential)"></span>
                                    <span class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400 tw:ml-2" x-text="credentialDetails(credential)"></span>
                                    <template x-for="message in errorsFor('credentials.' + index)">
                                        <span class="tw:block tw:text-sm tw:text-red-600 tw:dark:text-red-400" x-text="message"></span>
                                    </template>
                                </span>
                                <button type="button" class="btn btn-xs btn-default" @click="credentials.splice(index, 1)"
                                        title="{{ __('first_run.credentials.remove') }}" :aria-label="removeLabel(credentialName(credential))">
                                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                </button>
                            </li>
                        </template>
                    </ol>
                    <p class="tw:rounded-lg tw:border tw:border-dashed tw:border-gray-300 tw:dark:border-dark-gray-200 tw:px-4 tw:py-6 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400 tw:m-0"
                       x-show="credentials.length === 0">{{ __('first_run.credentials.empty') }}</p>
                    <p class="tw:text-sm tw:text-red-600 tw:dark:text-red-400 tw:mt-2 tw:mb-0" x-show="errorFor('credentials') && errorsFor('credentials.').length === 0" x-text="errorFor('credentials')"></p>

                    <button type="button" class="btn btn-default tw:mt-3" @click="openAdd()">
                        <i class="fa-solid fa-plus tw:mr-1" aria-hidden="true"></i> {{ __('first_run.credentials.add') }}
                    </button>

                    {{-- one form for every kind of credential --}}
                    <x-modal show="adding" max-width="md" :title="__('first_run.credentials.add')">
                        <form class="tw:space-y-4" @submit.prevent="saveNew()" id="first-run-credential">
                            <div>
                                <span class="tw:block tw:font-medium tw:mb-1.5">{{ __('first_run.credentials.version') }}</span>
                                <div class="btn-group tw:flex" role="radiogroup" aria-label="{{ __('first_run.credentials.version') }}">
                                    <button type="button" class="btn tw:flex-1" role="radio" :aria-checked="newType === 'community'"
                                            :class="newType === 'community' ? 'btn-primary' : 'btn-default'" @click="newType = 'community'; focusNew()">v1/v2c</button>
                                    <button type="button" class="btn tw:flex-1" role="radio" :aria-checked="newType === 'v3'"
                                            :class="newType === 'v3' ? 'btn-primary' : 'btn-default'" @click="newType = 'v3'; focusNew()">v3</button>
                                </div>
                            </div>
                            <div x-show="newType === 'community'" class="form-group tw:mb-0">
                                <label for="first-run-community" class="control-label">{{ __('first_run.credentials.community') }}</label>
                                <input type="text" id="first-run-community" class="form-control" x-model="newCommunity" autocomplete="off" spellcheck="false">
                            </div>
                            <div x-show="newType === 'v3'">
                                <x-field-schema-fields :fields="$v3Schema" model-prefix="v3Form" method-type="snmp-v3" id-prefix="first_run" />
                            </div>
                            <p class="tw:text-red-600 tw:dark:text-red-400 tw:mb-0" x-show="newError" x-text="newError" role="alert"></p>
                            <button type="submit" class="tw:hidden" tabindex="-1"></button>
                        </form>
                        <x-slot name="footer">
                            <button type="button" class="btn btn-default" @click="adding = false">{{ __('first_run.credentials.cancel') }}</button>
                            <button type="submit" form="first-run-credential" class="btn btn-primary">{{ __('first_run.credentials.save') }}</button>
                        </x-slot>
                    </x-modal>
                </section>

                {{-- Networks --}}
                <section x-show="step === 2">
                    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100 tw:mt-0 tw:mb-1 tw:outline-none" x-ref="heading2" tabindex="-1">{{ __('first_run.networks.heading') }}</h2>
                    <p class="tw:text-gray-600 tw:dark:text-dark-white-300 tw:mb-6 tw:text-pretty">
                        {{ __('first_run.networks.intro') }}
                        <span x-show="config.networks_suggested">{{ __('first_run.networks.suggested') }}</span>
                    </p>

                    <div class="form-group tw:mb-0" :class="(errorFor('networks') || tooLarge) && 'has-error'">
                        <label for="network-input" class="control-label tw:sr-only">{{ __('first_run.networks.heading') }}</label>
                        <div class="form-control tw:h-auto! tw:flex tw:flex-wrap tw:items-center tw:gap-2 tw:min-h-10 tw:cursor-text" @click="$refs.networkInput.focus()">
                            <template x-for="(network, index) in networks" :key="network">
                                <x-first-run.chip invalid="addressCount(network) === null"
                                                  remove="networks.splice(index, 1)" label="removeLabel(network)">
                                    <span x-text="network"></span>
                                    <span class="tw:text-xs tw:opacity-70" x-show="addressCount(network) !== null" x-text="addressCount(network)?.toLocaleString()"></span>
                                </x-first-run.chip>
                            </template>
                            <input id="network-input" type="text" x-ref="networkInput" x-model="networkInput" autocomplete="off" spellcheck="false"
                                   class="tw:flex-1 tw:min-w-56 tw:border-0 tw:outline-none tw:bg-transparent"
                                   placeholder="{{ __('first_run.networks.placeholder') }}"
                                   @keydown.enter.prevent="addNetwork()" @keydown.comma.prevent="addNetwork()" @keydown.space.prevent="addNetwork()" @blur="addNetwork()"
                                   @keydown.backspace="networkInput === '' && networks.pop()">
                        </div>
                        <div class="tw:flex tw:justify-between tw:gap-4">
                            <span class="help-block" x-text="errorFor('networks') || (tooLarge ? tooLargeMessage() : @js(__('first_run.networks.help')))"></span>
                            <span class="help-block tw:whitespace-nowrap" x-show="totalAddresses > 0"
                                  x-text="trans(@js(__('first_run.networks.addresses')), totalAddresses)"></span>
                        </div>
                    </div>
                </section>

                {{-- Scan --}}
                <section x-show="step === 3">
                    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100 tw:mt-0 tw:mb-3 tw:outline-none" x-ref="heading3" tabindex="-1"
                        x-text="scan.running ? @js(__('first_run.scan.heading_running')) : (scan.stopped ? @js(__('first_run.scan.heading_stopped')) : @js(__('first_run.scan.heading_done')))"></h2>

                    <div class="tw:h-2 tw:rounded-full tw:bg-gray-200 tw:dark:bg-dark-gray-300 tw:overflow-hidden" role="progressbar"
                         :aria-valuenow="percent()" aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('first_run.scan.heading_running') }}">
                        <div class="tw:h-full tw:rounded-full tw:transition-all tw:duration-300"
                             :class="scan.running ? 'tw:bg-blue-600' : (scan.stopped || scan.error ? 'tw:bg-amber-500' : 'tw:bg-green-600')" :style="`width: ${percent()}%`"></div>
                    </div>
                    <div class="tw:flex tw:flex-wrap tw:justify-between tw:gap-x-4 tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400 tw:mt-1 tw:mb-4">
                        <span x-text="countsText()"></span>
                        <span class="tw:tabular-nums" x-text="progressText()"></span>
                    </div>

                    {{-- Found hosts, newest first --}}
                    <ul class="tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-200 tw:max-h-96 tw:overflow-y-auto tw:p-0 tw:m-0 tw:list-none" aria-live="polite" x-show="scan.hosts.length > 0">
                        <template x-for="host in scan.hosts" :key="host.ip">
                            <li class="tw:flex tw:items-center tw:gap-3 tw:px-4 tw:py-3 tw:border-b tw:last:border-b-0 tw:border-gray-100 tw:dark:border-dark-gray-300 tw:animate-[fadeIn_0.4s_ease-out]">
                                <div class="tw:w-8 tw:h-8 tw:flex-none tw:flex tw:items-center tw:justify-center">
                                    <template x-if="host.device?.logo">
                                        <img :src="host.device.logo" alt="" class="tw:max-w-8 tw:max-h-8">
                                    </template>
                                    <template x-if="! host.device?.logo">
                                        <i class="fa-solid fa-lg" aria-hidden="true" :class="{
                                            'fa-circle-check tw:text-sky-600 tw:dark:text-sky-400': host.outcome === 'Known',
                                            'fa-circle-exclamation tw:text-red-600 tw:dark:text-red-400': host.outcome === 'Error',
                                            'fa-server tw:text-green-600': host.outcome === 'Added',
                                        }"></i>
                                    </template>
                                </div>
                                <div class="tw:min-w-0 tw:flex-1">
                                    <div class="tw:flex tw:items-baseline tw:gap-2 tw:min-w-0">
                                        <template x-if="host.device">
                                            <a :href="host.device.url" target="_blank" class="tw:font-semibold tw:truncate" x-text="host.device.name"></a>
                                        </template>
                                        <span class="tw:font-mono tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400" x-text="host.ip" x-show="host.device?.name !== host.ip"></span>
                                    </div>
                                    <div class="tw:text-sm tw:truncate"
                                         :class="host.outcome === 'Error' ? 'tw:text-red-600 tw:dark:text-red-400' : 'tw:text-gray-500 tw:dark:text-dark-white-400'"
                                         :title="host.device ? '' : host.message"
                                         x-text="host.device ? [host.device.os, host.device.hardware].filter(Boolean).join(' · ') : host.message"></div>
                                </div>
                                <span class="tw:flex-none tw:text-xs tw:font-medium tw:rounded-full tw:px-2 tw:py-0.5"
                                      :class="{
                                          'tw:bg-green-100 tw:text-green-800 tw:dark:bg-green-950 tw:dark:text-green-300': host.outcome === 'Added',
                                          'tw:bg-sky-100 tw:text-sky-800 tw:dark:bg-sky-950 tw:dark:text-sky-300': host.outcome === 'Known',
                                          'tw:bg-red-100 tw:text-red-800 tw:dark:bg-red-950 tw:dark:text-red-300': host.outcome === 'Error',
                                      }"
                                      x-text="outcomeLabel(host.outcome)"></span>
                            </li>
                        </template>
                    </ul>
                    <div class="tw:rounded-lg tw:border tw:border-dashed tw:border-gray-300 tw:dark:border-dark-gray-200 tw:px-4 tw:py-8 tw:text-center tw:text-gray-500 tw:dark:text-dark-white-400" x-show="scan.hosts.length === 0">
                        <span x-show="scan.running"><i class="fa-solid fa-spinner fa-spin tw:mr-2" aria-hidden="true"></i>{{ __('first_run.scan.waiting') }}</span>
                        <div x-show="! scan.running">
                            <div class="tw:font-semibold tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('first_run.scan.none_found') }}</div>
                            <div class="tw:text-sm tw:mt-1" x-show="scan.counts.ping_only === 0">{{ __('first_run.scan.none_found_help') }} {!! str_replace(':link', '<a href="https://docs.librenms.org/Support/SNMP-Configuration-Examples/" target="_blank" rel="noopener" class="tw:underline">' . e(__('first_run.snmp_docs')) . '</a>', e(__('first_run.scan.docs'))) !!}</div>
                        </div>
                    </div>

                    {{-- Finished --}}
                    <div x-show="! scan.running" class="tw:mt-4 tw:flex tw:flex-col tw:gap-3">
                        <div role="status">
                            <x-first-run.callout type="danger" x-show="scan.error"><span x-text="scan.error"></span></x-first-run.callout>
                            <x-first-run.callout type="warning" x-show="! scan.error && scan.stopped">{{ __('first_run.scan.stopped') }}</x-first-run.callout>
                            <x-first-run.callout type="success" x-show="! scan.error && ! scan.stopped && scan.seconds !== null && scan.counts.added > 0">
                                <strong x-text="summaryText()"></strong> {{ __('first_run.scan.discovering') }}
                            </x-first-run.callout>
                            <x-first-run.callout type="info" x-show="! scan.error && ! scan.stopped && scan.seconds !== null && scan.counts.added === 0">
                                <span x-text="summaryText()"></span>
                            </x-first-run.callout>
                        </div>

                        {{-- Hosts that replied to ping but not SNMP, most likely the credentials are wrong --}}
                        <x-first-run.callout type="warning" x-show="scan.counts.ping_only > 0">
                            <strong x-text="trans(@js(__('first_run.scan.ping_only_found')), scan.counts.ping_only)"></strong>
                            {{ __('first_run.scan.ping_only_help') }}
                            {!! str_replace(':link', '<a href="https://docs.librenms.org/Support/SNMP-Configuration-Examples/" target="_blank" rel="noopener" class="tw:underline">' . e(__('first_run.snmp_docs')) . '</a>', e(__('first_run.scan.docs'))) !!}
                            <div class="tw:flex tw:flex-wrap tw:gap-2 tw:mt-3">
                                <button type="button" class="btn btn-default btn-sm" @click="step = 1">
                                    <i class="fa-solid fa-key tw:mr-1" aria-hidden="true"></i> {{ __('first_run.scan.check_credentials') }}
                                </button>
                                <button type="button" class="btn btn-default btn-sm" @click="startScan(true)">
                                    <i class="fa-solid fa-heart-pulse tw:mr-1" aria-hidden="true"></i> {{ __('first_run.scan.add_ping_only') }}
                                </button>
                            </div>
                        </x-first-run.callout>

                        <p class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400 tw:mb-0" x-show="scan.counts.added + scan.counts.known > 0">
                            {{ __('first_run.next_steps.heading') }}:
                            @can('viewAny', \App\Models\AlertTransport::class)
                                <a href="{{ url('alert-transports') }}" target="_blank">{{ __('first_run.next_steps.alerts') }}</a> ·
                            @endcan
                            <a href="{{ route('validate') }}" target="_blank">{{ __('first_run.next_steps.validate') }}</a>
                        </p>
                    </div>
                </section>
            </div>

            {{-- Navigation, skip and finish complete the first run --}}
            <x-slot name="footer">
                <form method="POST" action="{{ route('first-run.finish') }}" class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2">
                    @csrf
                    <div>
                        <a href="{{ route('device.add') }}" class="btn btn-link tw:px-0" x-show="step === 0">{{ __('first_run.add_manually') }}</a>
                        <button type="button" class="btn btn-default" x-show="step === 1 || step === 2" @click="step--">
                            <i class="fa-solid fa-arrow-left tw:mr-1" aria-hidden="true"></i> {{ __('first_run.back') }}
                        </button>
                        <button type="button" class="btn btn-default" x-show="step === 3 && ! scan.running" @click="step = 2">
                            <i class="fa-solid fa-arrow-left tw:mr-1" aria-hidden="true"></i> {{ __('first_run.scan.change_networks') }}
                        </button>
                    </div>
                    <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                        <button type="submit" name="to" value="home" class="btn btn-link" x-show="step < 3">{{ __('first_run.skip') }}</button>
                        <button type="button" class="btn btn-primary" x-show="step === 0" @click="step = 1">
                            {{ __('first_run.welcome.start') }} <i class="fa-solid fa-arrow-right tw:ml-1" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn btn-primary" x-show="step === 1" @click="saveCredentials()" :disabled="busy">
                            <i class="fa-solid fa-spinner fa-spin tw:mr-1" x-show="busy" aria-hidden="true"></i>
                            {{ __('first_run.next') }} <i class="fa-solid fa-arrow-right tw:ml-1" x-show="! busy" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn btn-primary" x-show="step === 2" @click="startScan()" :disabled="busy || (networks.length === 0 && networkInput.trim() === '') || tooLarge">
                            <i class="fa-solid tw:mr-1" :class="busy ? 'fa-spinner fa-spin' : 'fa-magnifying-glass'" aria-hidden="true"></i>
                            {{ __('first_run.networks.start') }}
                        </button>
                        <button type="button" class="btn btn-default" x-show="step === 3 && scan.running" @click="stopScan()">
                            <i class="fa-solid fa-stop tw:mr-1" aria-hidden="true"></i> {{ __('first_run.scan.stop') }}
                        </button>
                        <button type="submit" name="to" value="home" class="btn" x-show="step === 3 && ! scan.running"
                                :class="scan.counts.added + scan.counts.known > 0 ? 'btn-default' : 'btn-primary'">{{ __('first_run.scan.dashboard') }}</button>
                        <button type="submit" name="to" value="devices" class="btn btn-primary" x-show="step === 3 && ! scan.running && scan.counts.added + scan.counts.known > 0">
                            {{ __('first_run.scan.view_devices') }} <i class="fa-solid fa-arrow-right tw:ml-1" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
            </x-slot>
        </x-panel>
    </div>
@endsection

@push('styles')
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }
    </style>
@endpush

@push('scripts')
    <script>
        function firstRun(config) {
            const stepNames = ['welcome', 'credentials', 'networks', 'scan'];
            const outcomeLabels = {
                Added: @js(__('first_run.scan.added')),
                Known: @js(__('first_run.scan.known')),
                Error: @js(__('first_run.scan.error')),
            };

            return {
                config,
                step: 0,
                reached: 0, // the furthest step, the steps before it can be opened again
                busy: false,
                errors: {},
                credentials: config.credentials.map(credential => ({...credential, key: 'id' + credential.id})),
                adding: false, // the add credential modal
                newType: 'community',
                newCommunity: '',
                v3Form: {},
                newError: null,
                nextKey: 0,
                sortKey: 0,
                networks: [...config.networks],
                networkInput: '',
                scan: null,
                controller: null,

                init() {
                    this.scan = this.newScan();

                    this.$watch('step', step => {
                        this.reached = Math.max(this.reached, step);
                        this.errors = {};
                        // with the path, the layout's <base href="/"> would make a bare #hash the home page
                        history.replaceState(null, '', location.pathname + location.search + (step > 0 ? '#' + stepNames[step] : ''));
                        // move focus to the new step for keyboard and screen reader users
                        this.$nextTick(() => {
                            window.scrollTo({top: 0, behavior: 'smooth'});
                            this.$refs['heading' + step]?.focus({preventScroll: true});
                        });
                    });

                    // the step is kept in the url so a reload stays on it, scan results are not kept so it opens the networks
                    const restored = stepNames.indexOf(location.hash.slice(1));
                    if (restored > 0) {
                        this.reached = Math.min(restored, 2);
                        this.step = this.reached;
                    }
                },

                newScan() {
                    return {running: false, stopped: false, error: null, icmp: false, total: 0, done: 0, seconds: null, hosts: [], counts: {added: 0, known: 0, ping_only: 0, no_reply: 0, errors: 0}};
                },

                canGoTo(step) {
                    return step !== this.step && step <= this.reached && ! this.scan.running && ! this.busy;
                },

                // pick the singular or plural of a translation like trans_choice()
                trans(choices, count) {
                    const [one, many] = choices.split('|');
                    return (count === 1 ? one : (many ?? one)).replace(':count', count.toLocaleString());
                },

                duration(seconds) {
                    const [instant, second, secondsMany] = @js(__('first_run.scan.seconds')).split('|');
                    if (seconds < 1) {
                        return instant;
                    }
                    const minutes = Math.floor(seconds / 60);
                    const rest = Math.round(seconds % 60);
                    const secondsText = (rest === 1 ? second : secondsMany).replace(':count', rest);
                    if (minutes === 0) {
                        return secondsText;
                    }
                    const minutesText = this.trans(@js(__('first_run.scan.minutes')), minutes);
                    return rest === 0 ? minutesText : minutesText + ' ' + secondsText;
                },

                errorFor(key) {
                    return this.errorsFor(key)[0] ?? null;
                },

                errorsFor(key) {
                    return Object.entries(this.errors).filter(([field]) => field === key || field.startsWith(key + '.')).map(([, messages]) => messages[0]);
                },

                credentialName(credential) {
                    return credential.type === 'v3' ? credential.authname : (credential.label ?? credential.community);
                },

                credentialDetails(credential) {
                    if (credential.type !== 'v3') {
                        return '';
                    }

                    const parts = [this.config.v3_levels[credential.authlevel] ?? credential.authlevel];
                    if (credential.authalgo && credential.authlevel !== 'noAuthNoPriv') {
                        parts.push(credential.authalgo);
                    }
                    if (credential.cryptoalgo && credential.authlevel === 'authPriv') {
                        parts.push(credential.cryptoalgo);
                    }
                    return parts.join(' · ');
                },

                openAdd() {
                    this.newCommunity = '';
                    this.v3Form = {...this.config.v3_defaults, authname: ''};
                    this.newError = null;
                    this.adding = true;
                    this.focusNew();
                },

                focusNew() {
                    this.newError = null;
                    this.$nextTick(() => document.getElementById(this.newType === 'v3' ? 'first_run_snmp-v3_authname' : 'first-run-community')?.focus());
                },

                // the same checks as the server, so a credential in the list can be saved
                saveNew() {
                    this.newError = this.newType === 'v3' ? this.checkV3(this.v3Form) : this.checkCommunity(this.newCommunity.trim());
                    if (this.newError) {
                        return;
                    }

                    const credential = this.newType === 'v3' ? this.v3Credential(this.v3Form) : {type: 'community', community: this.newCommunity.trim()};
                    this.credentials.push({key: 'new' + this.nextKey++, ...credential});
                    this.adding = false;
                },

                checkCommunity(community) {
                    if (community === '') {
                        return @js(__('first_run.credentials.community_required'));
                    }
                    if (this.credentials.some(credential => credential.type === 'community' && credential.community === community)) {
                        return @js(__('first_run.credentials.community_exists'));
                    }
                    return null;
                },

                checkV3(form) {
                    if (! form.authname?.trim()) {
                        return @js(__('first_run.credentials.v3_authname_required'));
                    }
                    if (form.authlevel !== 'noAuthNoPriv' && (form.authpass ?? '').length < 8) {
                        return @js(__('first_run.credentials.v3_authpass_required'));
                    }
                    if (form.authlevel === 'authPriv' && (form.cryptopass ?? '').length < 8) {
                        return @js(__('first_run.credentials.v3_cryptopass_required'));
                    }
                    return null;
                },

                v3Credential(form) {
                    const credential = {type: 'v3', authname: form.authname.trim(), authlevel: form.authlevel};
                    if (form.authlevel !== 'noAuthNoPriv') {
                        Object.assign(credential, {authpass: form.authpass, authalgo: form.authalgo});
                    }
                    if (form.authlevel === 'authPriv') {
                        Object.assign(credential, {cryptopass: form.cryptopass, cryptoalgo: form.cryptoalgo});
                    }
                    return credential;
                },

                removeLabel(name) {
                    return @js(__('first_run.credentials.remove')) + ' ' + name;
                },

                // after a drag the DOM was changed by the sort, so the items are rendered fresh
                move(items, from, to) {
                    items.splice(to, 0, ...items.splice(from, 1));
                    this.sortKey++;
                },

                addNetwork() {
                    for (const value of this.networkInput.split(/[\s,]+/)) {
                        if (value !== '' && ! this.networks.includes(value)) {
                            this.networks.push(value);
                        }
                    }
                    this.networkInput = '';
                },

                // the number of addresses scanned like IP::addressCount(), null when the network is not valid
                addressCount(network) {
                    const match = network.match(/^(\d{1,3}(?:\.\d{1,3}){3})\/(\d{1,2})$/);
                    if (match) {
                        const prefix = Number(match[2]);
                        if (prefix > 32 || match[1].split('.').some(octet => Number(octet) > 255)) {
                            return null;
                        }
                        const count = 2 ** (32 - prefix);
                        return count > 2 ? count - 2 : count;
                    }

                    const v6 = network.match(/^[0-9a-f:]+\/(\d{1,3})$/i);
                    if (v6 && network.includes(':') && Number(v6[1]) <= 128) {
                        const count = 2 ** (128 - Number(v6[1]));
                        return count > 2 ? count - 2 : count;
                    }

                    return null;
                },

                get totalAddresses() {
                    return this.networks.reduce((total, network) => total + (this.addressCount(network) ?? 0), 0);
                },

                get tooLarge() {
                    return this.totalAddresses > this.config.max_addresses;
                },

                tooLargeMessage() {
                    return @js(__('first_run.networks.too_large'))
                        .replace(':count', this.totalAddresses.toLocaleString())
                        .replace(':max', this.config.max_addresses.toLocaleString());
                },

                async post(url, body, signal) {
                    return fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(body),
                        signal,
                    });
                },

                async saveCredentials() {
                    this.busy = true;
                    this.errors = {};
                    try {
                        const response = await this.post(this.config.urls.credentials, {
                            credentials: this.credentials.map(({key, ...credential}) => credential.id ? {id: credential.id} : credential),
                        });
                        const data = await response.json();
                        if (! response.ok) {
                            this.errors = data.errors ?? {credentials: [data.message]};
                            return;
                        }
                        // the new credentials are saved now, they are not added again when going back
                        this.credentials = data.credentials.map(credential => ({...credential, key: 'id' + credential.id}));
                        this.busy = false;
                        this.step = 2;
                    } catch (e) {
                        this.errors = {credentials: [e.message]};
                    } finally {
                        this.busy = false;
                    }
                },

                // ping fallback adds the hosts that reply to ping but not SNMP, offered after a scan found them
                async startScan(pingFallback = false) {
                    this.addNetwork();
                    if (this.networks.length === 0 || this.tooLarge) {
                        return;
                    }

                    this.busy = true;
                    this.errors = {};
                    this.controller = new AbortController();

                    let response;
                    try {
                        response = await this.post(this.config.urls.networks, {networks: this.networks});
                        if (response.ok) {
                            this.config.networks_suggested = false;
                            response = await this.post(this.config.urls.scan, {ping_fallback: pingFallback}, this.controller.signal);
                        }
                    } catch (e) {
                        this.errors = {networks: [e.message]};
                        this.busy = false;
                        return;
                    }

                    if (! response.ok) {
                        const data = await response.json().catch(() => ({message: response.statusText}));
                        this.busy = false;
                        this.step = 2;
                        this.$nextTick(() => this.errors = data.errors ?? {networks: [data.message]});
                        return;
                    }

                    this.scan = this.newScan();
                    this.scan.running = true;
                    this.busy = false;
                    this.step = 3;

                    try {
                        await this.readLines(response.body, line => this.handleLine(line));
                        if (this.scan.seconds === null && this.scan.error === null) {
                            this.scan.error = @js(__('first_run.scan.failed'));
                        }
                    } catch (e) {
                        if (e.name === 'AbortError') {
                            this.scan.stopped = true;
                        } else {
                            this.scan.error = @js(__('first_run.scan.failed')) + ' ' + e.message;
                        }
                    } finally {
                        this.scan.running = false;
                    }
                },

                stopScan() {
                    this.controller?.abort();
                },

                async readLines(stream, onLine) {
                    const reader = stream.pipeThrough(new TextDecoderStream()).getReader();
                    let buffer = '';
                    while (true) {
                        const {value, done} = await reader.read();
                        if (done) {
                            break;
                        }
                        buffer += value;
                        const lines = buffer.split('\n');
                        buffer = lines.pop();
                        lines.filter(line => line.trim() !== '').forEach(line => onLine(JSON.parse(line)));
                    }
                },

                handleLine(line) {
                    if (line.type === 'start') {
                        this.scan.total = line.total;
                        this.scan.icmp = line.icmp;
                    } else if (line.type === 'done') {
                        this.scan.seconds = line.seconds;
                    } else if (line.type === 'error') {
                        this.scan.error = @js(__('first_run.scan.failed')) + ' ' + line.message;
                    } else if (line.type === 'result') {
                        this.scan.done++;
                        this.scan.counts[this.counter(line.outcome)]++;
                        if (line.message !== undefined) {
                            // newest first, so the latest is always in view
                            this.scan.hosts.unshift({ip: line.ip, outcome: line.outcome, message: line.message, device: line.device ?? null});
                        }
                    }
                },

                counter(outcome) {
                    if (outcome === 'Unreachable' && this.scan.icmp) {
                        return 'ping_only'; // pinged, so it replied to ping but not SNMP
                    }

                    return {Added: 'added', Known: 'known', Error: 'errors'}[outcome] ?? 'no_reply';
                },

                countsText() {
                    const counts = this.scan.counts;
                    return [
                        this.trans(@js(__('first_run.scan.added_count')), counts.added),
                        counts.known > 0 ? this.trans(@js(__('first_run.scan.known_count')), counts.known) : null,
                        counts.errors > 0 ? this.trans(@js(__('first_run.scan.errors_count')), counts.errors) : null,
                    ].filter(Boolean).join(' · ');
                },

                percent() {
                    return this.scan.total ? Math.floor(this.scan.done / this.scan.total * 100) : 0;
                },

                progressText() {
                    return @js(__('first_run.scan.progress'))
                        .replace(':done', this.scan.done.toLocaleString())
                        .replace(':total', this.scan.total.toLocaleString());
                },

                summaryText() {
                    const text = this.scan.counts.added > 0
                        ? this.trans(@js(__('first_run.scan.summary')), this.scan.counts.added)
                        : @js(__('first_run.scan.no_new'));

                    return text.replace(':duration', this.duration(this.scan.seconds));
                },

                outcomeLabel(outcome) {
                    return outcomeLabels[outcome] ?? outcome;
                },
            };
        }
    </script>
@endpush
