<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

require_once 'includes/print-alert-templates.php';
?>

<form action="" method="post">
    <input type="hidden" name="tool" value="convert">
    <input type="submit" class="btn btn-primary btn-sm" value="Convert template">
</form>
<br />
<?php
if ($vars['tool'] === 'convert' || $vars['old']) {
    if ($vars['old']) {
        foreach (explode(PHP_EOL, $vars['old']) as $line) {
            if (str_contains($line, '{calc')) {
                $new .= preg_replace(
                    [
                        '/{calc[ ]*([\w\d\s\%\.\(\)\*\/\-\+\/]+)}/',// Replaces {calc (something*100)}
                        '/%([\w\d]+)\.([\w\d]+)/',// Replaces %something.anything
                    ],
                    [
                        '@php echo \1; @endphp ',
                        '$value[\'\2\']',
                    ],
                    $line
                );
            } else {
                $new .= convert_template($line);
            }
        }
    }
    echo "
    <form action='' method='post'>
        <div class='row'>
            <div class='col-sm-6'>
                Paste old template here<br />
                <textarea name='old' cols='80' rows='30'>{$vars['old']}</textarea>
            </div>
            <div class='col-sm-6'>
                New<br />
                <textarea name='old' cols='80' rows='30' disabled>$new</textarea>
            </div>
        </div>
        <button type='submit' class='btn btn-success btn-sm' name='submit' value='convert'>Convert</button>
    </form>
    ";
}

function convert_template($old)
{
    $find = [
        '/{if %([\w=\s]+)}/',// Replaces {if %something == else}
        '/{else}/',// Replaces {else}
        '/{\/if}/',// Replaces {/if}
        '/{foreach %faults}/',// Replaces {foreach %faults}
        '/{foreach %contacts}/',// Replaces {foreach %contacts}
        '/{\/foreach}/',// Replaces {/foreach}
        '/{calc[ ]*([\w\d\s\%\.\(\)\*\/\-\+\/]+)}/',// Replaces {calc (something*100)}
        '/%value.string/',// Replaces %value.string
        '/%([\w\d]+)\.([\w\d]+)/',// Replaces %something.anything
        '/%([\w\d]+)/',// Replaces %anything
    ];
    $replace = [
        '@if ($alert->\1) ',
        ' @else ',
        ' @endif',
        '@foreach ($alert->faults as $key => $value)',
        '@foreach ($alert->contacts as $key => $value)',
        '@endforeach ',
        '@php echo \1; @endphp ',
        '$value[\'string\']',
        '{{ $\1[\'\2\'] }}',
        '{{ $alert->\1 }}',
    ];
    $old = preg_replace($find, $replace, $old);

    // Revert some over-zealous changes:
    $find = [
        '/\$alert->key/',
        '/\$alert->value/',
    ];
    $replace = [
        '$key',
        '$value',
    ];
    return preg_replace($find, $replace, $old);
}