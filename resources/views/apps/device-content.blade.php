@if($page->filters || $page->details || $page->text)
    <x-panel>
        @foreach($page->filters as $filter)
            <div class="tw:flex tw:flex-wrap tw:items-baseline tw:gap-x-3 tw:gap-y-2 tw:py-1">
                <strong>{{ $filter['label'] }}</strong>
                @foreach($filter['options'] as $option)
                    <a href="{{ $option['link'] }}"
                        @class(['pagemenu-selected' => $option['selected']])
                        @if($option['selected']) aria-current="page" @endif>{{ $option['text'] }}</a>
                @endforeach
            </div>
        @endforeach

        @if($page->details)
            <dl class="tw:my-2">
                @foreach($page->details as $label => $value)
                    <div class="tw:flex tw:flex-wrap tw:gap-2">
                        <dt>{{ $label }}</dt>
                        <dd class="tw:whitespace-pre-wrap">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif

        @foreach($page->text as $label => $text)
            @if($label !== '')
                <h4>{{ $label }}</h4>
            @endif
            <pre>{{ $text }}</pre>
        @endforeach
    </x-panel>
@endif

@foreach($page->tables as $table)
    <x-panel :title="$table['title']">
        <x-slot:table>
            <div class="table-responsive">
                @include('widgets.sortable_table', ['headers' => $table['headers'], 'rows' => $table['rows']])
            </div>
        </x-slot:table>
    </x-panel>
@endforeach

{{ $page->content }}

@foreach($page->graphs as $graph)
    <x-panel :title="$graph['title']" :id="$graph['anchor']">
        <x-graph-row :type="$graph['type']" :vars="$graph['vars']" :graphs="$periods"
            columns="responsive" loading="lazy" legend="yes" />
    </x-panel>
@endforeach

@if($page->notice)
    <p class="text-muted">{{ $page->notice }}</p>
@elseif(! $page->hasContent())
    <p class="text-muted">{{ __('No graphs available for this application.') }}</p>
@endif
