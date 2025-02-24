<div class="panel panel-default">
    <div class="panel-heading mb-3">
        <h4 class="font-weight-bold">{{ $title }} »</h4>
    </div>
    <div class="panel-body">
        <div id="menuAccordion" class="accordion">
            @foreach ($menu as $header => $m)
                <div class="card mb-2">
                    <div class="card-header bg-light" id="heading-{{ Str::slug($header) }}">
                        <h5 class="mb-0">
                            <button 
                                class="btn btn-link text-dark font-weight-bold" 
                                type="button" 
                                data-toggle="collapse" 
                                data-target="#collapse-{{ Str::slug($header) }}" 
                                aria-expanded="{{ collect($m)->pluck('url')->contains(request()->route('vars')) ? 'true' : 'false' }}" 
                                aria-controls="collapse-{{ Str::slug($header) }}">
                                {{ $header }}
                            </button>
                        </h5>
                    </div>

                    <div 
                        id="collapse-{{ Str::slug($header) }}" 
                        class="collapse @if(collect($m)->pluck('url')->contains(request()->route('vars'))) show @endif" 
                        aria-labelledby="heading-{{ Str::slug($header) }}" 
                        data-parent="#menuAccordion">
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($m as $sm)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <a 
                                            href="{{ route('device', ['device' => $device_id, 'tab' => $current_tab, 'vars' => $sm['url']]) }}" 
                                            class="text-dark @if(request()->route('vars') == $sm['url']) font-weight-bold text-primary @endif">
                                            {{ $sm['name'] }}
                                        </a>

                                        @isset($sm['sub_name'])
                                            <span>
                                                (<a 
                                                    href="{{ route('device', ['device' => $device_id, 'tab' => $current_tab, 'vars' => $sm['sub_url']]) }}" 
                                                    class="text-muted @if(request()->route('vars') == $sm['sub_url']) text-primary font-weight-bold @endif">
                                                    {{ $sm['sub_name'] }}
                                                </a>)
                                            </span>
                                        @endisset
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
