@props([
    'paginator', // \Illuminate\Contracts\Pagination\LengthAwarePaginator
    'options' => [], // list<int> per page choices
    'perPage' => null, // int selected per page, defaults to the paginator's
])
<div class="tw:p-3 tw:flex tw:items-center tw:justify-between tw:flex-wrap tw:gap-4">
    <div class="tw:flex tw:items-center tw:gap-2 tw:text-sm">
        <label for="per_page" class="tw:whitespace-nowrap">{{ __('Per page') }}</label>
        <select id="per_page" class="form-control input-sm tw:w-auto" onchange="window.location.href = $(this).val()">
            @foreach($options as $option)
                <option value="{{ request()->fullUrlWithQuery(['per_page' => $option, 'page' => 1]) }}" @selected(($perPage ?? $paginator->perPage()) == $option)>{{ $option }}</option>
            @endforeach
        </select>
    </div>
    <div>{{ $paginator->appends(request()->all())->links() }}</div>
</div>
