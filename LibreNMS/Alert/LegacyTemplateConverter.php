<?php

namespace LibreNMS\Alert;

/**
 * Convert alert templates from the old {if}/%var syntax to Blade.
 */
class LegacyTemplateConverter
{
    public static function isLegacy(?string $template): bool
    {
        return str_contains((string) $template, '{/if}');
    }

    public function convert(string $template): string
    {
        return implode(PHP_EOL, array_map($this->convertLine(...), explode(PHP_EOL, $template)));
    }

    private function convertLine(string $line): string
    {
        if (str_contains($line, '{calc')) {
            return (string) preg_replace(
                [
                    '/{calc[ ]*([\w\d\s\%\.\(\)\*\/\-\+\/]+)}/', // {calc (something*100)}
                    '/%([\w\d]+)\.([\w\d]+)/', // %something.anything
                ],
                [
                    "@php\necho \\1;\n@endphp ",
                    '$value[\'\2\']',
                ],
                $line
            );
        }

        $line = (string) preg_replace([
            '/{if %([\w=\s]+)}/', // {if %something == else}
            '/{else}/',
            '/{\/if}/',
            '/{foreach %faults}/',
            '/{foreach %contacts}/',
            '/{\/foreach}/',
            '/{calc[ ]*([\w\d\s\%\.\(\)\*\/\-\+\/]+)}/', // {calc (something*100)}
            '/%value.string/',
            '/%([\w\d]+)\.([\w\d]+)/', // %something.anything
            '/%([\w\d]+)/', // %anything
        ], [
            ' @if ($alert->\1) ',
            ' @else ',
            ' @endif ',
            ' @foreach ($alert->faults as $key => $value)',
            ' @foreach ($alert->contacts as $key => $value)',
            ' @endforeach ',
            " @php\necho \\1;\n@endphp ",
            '{{ $value[\'string\'] }}',
            '{{ $\1[\'\2\'] }}',
            '{{ $alert->\1 }}',
        ], $line);

        // revert over-zealous changes
        return (string) preg_replace(['/\$alert->key/', '/\$alert->value/'], ['$key', '$value'], $line);
    }
}
