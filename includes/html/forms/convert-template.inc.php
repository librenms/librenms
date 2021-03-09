<?php

//FIXME remove Deprecated template

/**
 * convert-template.inc.php
 *
 * Ajax method to convert templates from the old syntax to the blade syntax
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Neil Lathwood
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use Illuminate\Support\Str;

header('Content-type: application/json');

if (! Auth::user()->hasGlobalAdmin()) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'You need to be admin',
    ]));
}

if (empty($vars['template'])) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'No template to convert',
    ]));
}

$new_body = '';
foreach (explode(PHP_EOL, $vars['template']) as $line) {
    $new_body .= convert_template($line) . PHP_EOL;
}
$new_title = convert_template($vars['title']);

function convert_template($line)
{
    if (Str::contains($line, '{calc')) {
        return preg_replace(
            [
                '/{calc[ ]*([\w\d\s\%\.\(\)\*\/\-\+\/]+)}/', // Replaces {calc (something*100)}
                '/%([\w\d]+)\.([\w\d]+)/', // Replaces %something.anything
            ],
            [
                "@php\necho \\1;\n@endphp ",
                '$value[\'\2\']',
            ],
            $line
        );
    }

    $old1 = $line;
    $find = [
        '/{if %([\w=\s]+)}/', // Replaces {if %something == else}
        '/{else}/', // Replaces {else}
        '/{\/if}/', // Replaces {/if}
        '/{foreach %faults}/', // Replaces {foreach %faults}
        '/{foreach %contacts}/', // Replaces {foreach %contacts}
        '/{\/foreach}/', // Replaces {/foreach}
        '/{calc[ ]*([\w\d\s\%\.\(\)\*\/\-\+\/]+)}/', // Replaces {calc (something*100)}
        '/%value.string/', // Replaces %value.string
        '/%([\w\d]+)\.([\w\d]+)/', // Replaces %something.anything
        '/%([\w\d]+)/', // Replaces %anything
    ];
    $replace = [
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
    ];
    $old1 = preg_replace($find, $replace, $old1);

    // Revert some over-zealous changes:
    $find = [
        '/\$alert->key/',
        '/\$alert->value/',
    ];
    $replace = [
        '$key',
        '$value',
    ];

    return preg_replace($find, $replace, $old1);
}

exit(json_encode([
    'status' => 'ok',
    'message' => 'Template converted, review and save to update',
    'template' => $new_body,
    'title' => $new_title,
]));
