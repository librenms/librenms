@props([
    'responsive' => true,
    'condensed' => true,
    'hover' => true,
    'striped' => false,
    'columns' => [],
    'rows' => null,
    'empty' => null,
])

@php
    $tableClasses = [
        'table',
        'table-condensed' => $condensed,
        'table-hover' => $hover,
        'table-striped' => $striped,
    ];
@endphp

@if($responsive)
    <div class="table-responsive">
@endif
        <table {{ $attributes->class($tableClasses) }}>
            @if(isset($head))
                <thead {{ $head->attributes }}>
                    {{ $head }}
                </thead>
            @elseif(! empty($columns))
                <thead>
                    <tr>
                        @foreach($columns as $colKey => $colDef)
                            @if(is_array($colDef))
                                <th @foreach($colDef as $attrKey => $attrVal)
                                        @if(! in_array($attrKey, ['label', 'name', 'field', 'key', 'title']) && is_scalar($attrVal))
                                            {{ $attrKey }}="{{ $attrVal }}"
                                        @endif
                                    @endforeach
                                    @isset($colDef['title']) title="{{ $colDef['title'] }}" @endisset
                                >
                                    {{ $colDef['label'] ?? $colDef['name'] ?? (is_string($colKey) ? $colKey : '') }}
                                </th>
                            @else
                                <th>{{ $colDef }}</th>
                            @endif
                        @endforeach
                    </tr>
                </thead>
            @endif

            @if(isset($body))
                <tbody {{ $body->attributes }}>
                    @if($empty !== null && (isset($rows) ? (is_countable($rows) ? count($rows) === 0 : false) : false))
                        <tr>
                            <td colspan="{{ count($columns) ?: 100 }}" class="tw:text-center tw:p-5 text-muted">
                                <em>{{ $empty }}</em>
                            </td>
                        </tr>
                    @else
                        {{ $body }}
                        @if($empty !== null && empty(trim((string) $body)))
                            <tr>
                                <td colspan="{{ count($columns) ?: 100 }}" class="tw:text-center tw:p-5 text-muted">
                                    <em>{{ $empty }}</em>
                                </td>
                            </tr>
                        @endif
                    @endif
                </tbody>
            @elseif(isset($slot) && $slot->isNotEmpty())
                {{ $slot }}
            @elseif($rows !== null)
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            @foreach($columns as $colKey => $colDef)
                                @php
                                    $key = is_array($colDef) ? ($colDef['key'] ?? $colDef['field'] ?? $colKey) : (is_string($colKey) ? $colKey : $colDef);
                                    $val = is_array($row) ? ($row[$key] ?? null) : ($row->{$key} ?? null);
                                @endphp
                                <td>{{ $val }}</td>
                            @endforeach
                        </tr>
                    @empty
                        @if($empty !== null)
                            <tr>
                                <td colspan="{{ count($columns) }}" class="tw:text-center tw:p-5 text-muted">
                                    <em>{{ $empty }}</em>
                                </td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
            @endif
        </table>
@if($responsive)
    </div>
@endif
