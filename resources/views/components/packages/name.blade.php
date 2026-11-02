@props(['name'])
<td class="tw:font-semibold">
    <a href="{{ \App\Models\Package::urlForName($name) }}">{{ $name }}</a>
</td>
