{{-- a column the list can be sorted by, $sort and $direction are the current order --}}
<th aria-sort="{{ $sort !== $key ? 'none' : ($direction === 'asc' ? 'ascending' : 'descending') }}">
    <button type="button" class="tw:font-bold" @click="setSort('{{ $key }}')">{{ $label }}{{ $sort !== $key ? '' : ($direction === 'asc' ? ' ▲' : ' ▼') }}</button>
</th>
