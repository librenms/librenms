<table>
    <tbody>
        @foreach($rows as $key => $value)
            <tr>
                <td class="tw:bg-gray-100 tw:dark:bg-dark-gray-300 tw:font-semibold tw:align-top tw:px-3 tw:py-1 tw:whitespace-nowrap">{{ $key }}</td>
                <td class="tw:bg-gray-50 tw:dark:bg-dark-gray-400 tw:px-3 tw:py-1">{{ $value }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
