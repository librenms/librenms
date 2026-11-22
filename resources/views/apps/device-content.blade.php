@if($page->filters)
    <x-panel>
        <x-slot:heading>
            @foreach($page->filters as $filter)
                <x-option-bar :name="$filter['label']" :options="$filter['options']" border="none" />
            @endforeach
        </x-slot:heading>
    </x-panel>
@endif

@if($page->details)
    <x-panel>
        <x-slot:table>
            <x-table :hover="false">
                <x-slot:body>
                    @foreach($page->details as $label => $value)
                        <tr>
                            <th scope="row" class="tw:w-1/4">{{ $label }}</th>
                            <td class="tw:whitespace-pre-wrap">{{ $value }}</td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table>
        </x-slot:table>
    </x-panel>
@endif

@foreach($page->text as $label => $text)
    <x-panel :title="$label">
        <pre class="tw:mb-0">{{ $text }}</pre>
    </x-panel>
@endforeach

@foreach($page->tables as $table)
    <x-panel :title="$table['title']">
        <x-slot:table>
            <x-table :columns="$table['headers']" :rows="$table['rows']" :empty="__('No data')" striped>
                <x-slot:body>
                    @foreach($table['rows'] as $row)
                        <tr>
                            @foreach($row as $value)
                                <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-table>
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
    <x-panel><em>{{ $page->notice }}</em></x-panel>
@elseif(! $page->hasContent())
    <x-panel><em>{{ __('No graphs available for this application.') }}</em></x-panel>
@endif
