@php($parsedIp = \LibreNMS\Util\IP::parse($ip, true))
<a href="{{ route('search', ['q' => $parsedIp?->compressed() ?? $ip]) }}" class="tw:font-mono tw:whitespace-nowrap">{{ $parsedIp?->compressed() ?? $ip }}{{ isset($prefix) ? "/$prefix" : '' }}</a>
