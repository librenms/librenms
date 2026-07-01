<?php

/**
 * Shared debug-panel helpers for app/device debug views.
 *
 * Functions:
 *   debug_collapsible(string $id, string $label, string ...$panels): void
 *     Renders the collapsible toggle button and wraps panels inside a collapse div.
 *
 *   debug_panel(string $title, string $body, string $toolbar = ''): string
 *     Returns one Bootstrap panel (heading + body) as an HTML string.
 *
 *   debug_toolbar(string $textId, string $filename, string $mimeType = 'application/json'): string
 *     Returns copy-to-clipboard + download buttons for a <pre>/<textarea> element
 *     whose id is $textId. The download href is built from the element content at
 *     click time via a small inline script.
 *
 *   debug_pre(string $id, string $content): string
 *     Returns a scrollable <pre> element with the given id and escaped content.
 */

/**
 * Return the toggle button for a collapsible debug section (no wrapper div).
 * Place this inside an optionbar or navbar using a pull-right span.
 *
 * @param  string  $id  HTML id of the collapse target
 * @param  string  $label  Button label text
 */
function debug_toggle_button(string $id, string $label = 'Debug'): string
{
    return <<<HTML
        <a class="btn btn-xs btn-default" data-toggle="collapse" href="#{$id}"
           aria-expanded="false" aria-controls="{$id}">
            <i class="fa fa-bug"></i> {$label}
        </a>
        HTML;
}

/**
 * Render the collapse wrapper div that holds the debug panels.
 * Pair with debug_toggle_button() when the button lives elsewhere (e.g. navbar).
 *
 * @param  string  $id  Must match the id passed to debug_toggle_button()
 * @param  string  ...$panels  Rendered panel HTML strings
 */
function debug_collapse_div(string $id, string ...$panels): void
{
    $inner = implode("\n", $panels);
    echo <<<HTML
        <div id="{$id}" class="collapse">
            {$inner}
        </div>
        HTML;
}

/**
 * Convenience wrapper: renders button + collapse div together.
 * Use debug_toggle_button() + debug_collapse_div() separately when the
 * button needs to live in a navbar.
 *
 * @param  string  $id  HTML id for the collapse target (must be unique on page)
 * @param  string  $label  Button label text
 * @param  string  ...$panels  HTML strings — each already a complete panel block
 */
function debug_collapsible(string $id, string $label, string ...$panels): void
{
    $btn = debug_toggle_button($id, $label);
    $inner = implode("\n", $panels);
    echo <<<HTML
        <div class="text-right" style="margin-bottom:6px">{$btn}</div>
        <div id="{$id}" class="collapse">
            {$inner}
        </div>
        HTML;
}

/**
 * Return one Bootstrap panel as an HTML string.
 *
 * @param  string  $title  Panel heading text (not escaped — caller must escape if needed)
 * @param  string  $body  Panel body HTML
 * @param  string  $toolbar  Optional HTML prepended inside the body (e.g. copy/export buttons)
 */
function debug_panel(string $title, string $body, string $toolbar = ''): string
{
    $toolbarHtml = $toolbar !== '' ? "<div class=\"text-right\" style=\"margin-bottom:8px\">{$toolbar}</div>" : '';

    return <<<HTML
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">{$title}</h3></div>
            <div class="panel-body">
                {$toolbarHtml}{$body}
            </div>
        </div>
        HTML;
}

/**
 * Return a scrollable <pre> element.
 *
 * @param  string  $id  HTML id (referenced by debug_toolbar)
 * @param  string  $content  Already HTML-escaped content
 */
function debug_pre(string $id, string $content): string
{
    return <<<HTML
        <pre id="{$id}" style="max-height:260px;overflow:auto">{$content}</pre>
        HTML;
}

/**
 * Render the debug collapse div (button is expected to already be in the page).
 * Equivalent to debug_collapse_div(); exists so callers don't need to know the internal name.
 *
 * @param  string  $id  Must match the id passed to debug_toggle_button()
 * @param  string  ...$panels  Rendered panel HTML strings
 */
function debug_render(string $id, string ...$panels): void
{
    debug_collapse_div($id, ...$panels);
}

/**
 * Format an array of datastore name strings as a comma-separated HTML snippet.
 *
 * @param  string[]  $stores  List of datastore names
 */
function debug_format_datastore_list(array $stores): string
{
    if (empty($stores)) {
        return '<span class="text-muted">none</span>';
    }

    return implode(', ', array_map(htmlspecialchars(...), $stores));
}

/**
 * Encode headers + rows as a CSV data URI suitable for an <a download> href.
 *
 * @param  string[]  $headers  Column header strings
 * @param  string[][]  $rows  2-D array of cell values (already cast to string by caller)
 */
function debug_csv_data_uri(array $headers, array $rows): string
{
    $escape = static fn (string $v): string => '"' . str_replace('"', '""', $v) . '"';

    $lines = [implode(',', array_map($escape, $headers))];
    foreach ($rows as $row) {
        $lines[] = implode(',', array_map(static fn ($v) => $escape((string) $v), $row));
    }

    return 'data:text/csv;base64,' . base64_encode(implode("\r\n", $lines));
}

/**
 * Return the last recorded data point from an RRD file, or null if unavailable.
 *
 * @param  string  $rrdFile  Absolute path to the RRD file
 * @return \LibreNMS\Data\Store\TimeSeriesPoint|null
 */
function debug_rrd_last_point(string $rrdFile): ?\LibreNMS\Data\Store\TimeSeriesPoint
{
    try {
        return App\Facades\Rrd::lastUpdate($rrdFile);
    } catch (Throwable) {
        return null;
    }
}

/**
 * Return a Bootstrap table panel from an array of flat associative rows.
 *
 * Column headers are derived from the keys of the first row.
 * Optionally adds an Export CSV toolbar button when $csvFilename is provided.
 *
 * @param  string  $title  Panel heading text (not HTML-escaped — caller escapes if needed)
 * @param  iterable  $rows  Array/Collection of flat associative arrays; values cast to string
 * @param  string|null  $csvFilename  When set, adds a CSV download button with this filename
 */
function debug_db_table_panel(string $title, iterable $rows, ?string $csvFilename = null): string
{
    $rows = is_array($rows) ? $rows : iterator_to_array($rows);

    if (empty($rows)) {
        return debug_panel($title, '<p class="text-muted" style="font-size:12px">No rows.</p>');
    }

    $columns = array_keys((array) reset($rows));
    $theadHtml = implode('', array_map(
        static fn ($h) => '<th>' . htmlspecialchars((string) $h) . '</th>',
        $columns
    ));
    $tbodyHtml = '';
    foreach ($rows as $row) {
        $row = (array) $row;
        $cells = implode('', array_map(
            static fn ($col) => '<td>' . htmlspecialchars((string) ($row[$col] ?? '')) . '</td>',
            $columns
        ));
        $tbodyHtml .= "<tr>{$cells}</tr>\n";
    }

    $body = <<<HTML
        <table class="table table-condensed table-hover" style="font-size:12px">
            <thead><tr>{$theadHtml}</tr></thead>
            <tbody>{$tbodyHtml}</tbody>
        </table>
        HTML;

    $toolbar = '';
    if ($csvFilename !== null) {
        $csvRows = array_map(
            static fn ($row) => array_map(static fn ($v) => (string) $v, array_values((array) $row)),
            $rows
        );
        $csvUri = debug_csv_data_uri($columns, $csvRows);
        $filenameEsc = htmlspecialchars($csvFilename, ENT_QUOTES);
        $toolbar = <<<HTML
            <a class="btn btn-xs btn-default" href="{$csvUri}" download="{$filenameEsc}">
                <i class="fa fa-download"></i> Export CSV
            </a>
            HTML;
    }

    return debug_panel($title, $body, $toolbar);
}

/**
 * Return the body HTML for an RRD files debug panel (no debug_panel() wrapper).
 *
 * Renders the active-datastores line, the RRD files table, and the current DS value cards.
 * Wrap the return value in debug_panel() to add a heading and optional toolbar.
 *
 * Expected shape of each $rrdEntries element:
 *   ['array' => string, 'rrd_file' => string, 'exists' => bool,
 *    'file'  => ['size_bytes' => int|null, 'modified_at' => string|null],
 *    'last_update' => ['timestamp_iso' => string, 'data' => array]|null]
 *
 * @param  array  $rrdEntries  RRD file descriptor array (as built by the app's stored-data helper)
 * @param  string[]  $stores  Active datastore names for the header line
 */
function debug_rrd_files_panel(array $rrdEntries, array $stores = []): string
{
    static $fileHeaders = ['Array', 'Exists', 'Last update', 'File mtime', 'Size (SI)', 'RRD file'];

    $filesRows = '';
    foreach ($rrdEntries as $entry) {
        $array = htmlspecialchars((string) ($entry['array'] ?? ''));
        $rrdFile = htmlspecialchars((string) ($entry['rrd_file'] ?? ''));
        $exists = ! empty($entry['exists']) ? 'yes' : 'no';
        $existsClass = $exists === 'yes' ? 'text-success' : 'text-danger';
        $modifiedAt = htmlspecialchars((string) ($entry['file']['modified_at'] ?? '-'));
        $size = isset($entry['file']['size_bytes'])
            ? LibreNMS\Util\Number::formatSi((float) $entry['file']['size_bytes'], 2, 0, 'B')
            : '-';
        $lastUpdateTs = htmlspecialchars((string) ($entry['last_update']['timestamp_iso'] ?? '-'));

        $filesRows .= <<<HTML
            <tr>
                <td>{$array}</td>
                <td><span class="{$existsClass}">{$exists}</span></td>
                <td style="white-space:nowrap">{$lastUpdateTs}</td>
                <td style="white-space:nowrap">{$modifiedAt}</td>
                <td>{$size}</td>
                <td style="font-family:monospace;word-break:break-all">{$rrdFile}</td>
            </tr>
            HTML;
    }

    $theadHtml = implode('', array_map(static fn ($h) => '<th>' . htmlspecialchars((string) $h) . '</th>', $fileHeaders));
    $colspan = count($fileHeaders);

    if ($filesRows === '') {
        $filesRows = "<tr><td colspan=\"{$colspan}\" class=\"text-muted\">No RRD files discovered.</td></tr>";
    }

    $dsCards = '';
    foreach ($rrdEntries as $entry) {
        $array = htmlspecialchars((string) ($entry['array'] ?? ''));
        $data = (array) ($entry['last_update']['data'] ?? []);

        if (empty($data)) {
            continue;
        }

        $dsRows = '';
        foreach ($data as $dataset => $value) {
            $dsEsc = htmlspecialchars((string) $dataset);
            $valEsc = $value === null ? '<span class="text-muted">null</span>' : htmlspecialchars((string) $value);
            $dsRows .= "<tr><td style=\"padding:2px 8px 2px 0\">{$dsEsc}</td><td style=\"padding:2px 0\">{$valEsc}</td></tr>\n";
        }

        $dsCards .= <<<HTML
            <div style="margin-right:16px;margin-bottom:12px;min-width:180px">
                <div style="font-size:11px;font-weight:bold;margin-bottom:4px;color:#555">{$array}</div>
                <table style="font-size:12px;border-collapse:collapse">{$dsRows}</table>
            </div>
            HTML;
    }

    $currentHtml = $dsCards !== ''
        ? "<div style=\"display:flex;flex-wrap:wrap;align-items:flex-start\">{$dsCards}</div>"
        : '<p class="text-muted" style="font-size:12px">No current DS values available.</p>';

    $datastoreList = debug_format_datastore_list($stores);

    return <<<HTML
        <div class="text-muted" style="margin-bottom:8px;font-size:12px">Active datastores: {$datastoreList}</div>
        <h4 style="margin-top:0">RRD Files</h4>
        <table class="table table-condensed table-hover" style="font-size:12px">
            <thead><tr>{$theadHtml}</tr></thead>
            <tbody>{$filesRows}</tbody>
        </table>
        <h4>Current DS Values</h4>
        {$currentHtml}
        HTML;
}

/**
 * Return a complete Bootstrap panel for a sensor/metric table.
 *
 * @param  string  $title  Panel heading (not HTML-escaped)
 * @param  string[]  $columns  Ordered list of column keys to display (defines headers and extraction order)
 * @param  array  $rows  Array of associative arrays keyed by $columns values
 * @param  string  $csvFilename  When non-empty, adds a CSV download button with this filename
 */
function debug_sensors_panel(string $title, array $columns, array $rows, string $csvFilename = ''): string
{
    if (empty($rows)) {
        return debug_panel($title, '<p class="text-muted">No rows found.</p>');
    }

    $theadHtml = implode('', array_map(
        static fn ($h) => '<th>' . htmlspecialchars($h) . '</th>',
        $columns
    ));
    $tbodyHtml = '';
    foreach ($rows as $row) {
        $cells = implode('', array_map(
            static fn ($col) => '<td>' . htmlspecialchars((string) ($row[$col] ?? '')) . '</td>',
            $columns
        ));
        $tbodyHtml .= "<tr>{$cells}</tr>\n";
    }

    $body = <<<HTML
        <table class="table table-condensed table-hover" style="font-size:12px">
            <thead><tr>{$theadHtml}</tr></thead>
            <tbody>{$tbodyHtml}</tbody>
        </table>
        HTML;

    $toolbar = '';
    if ($csvFilename !== '') {
        $csvRows = array_map(
            static fn ($row) => array_map(static fn ($col) => (string) ($row[$col] ?? ''), $columns),
            $rows
        );
        $csvUri = debug_csv_data_uri($columns, $csvRows);
        $filenameEsc = htmlspecialchars($csvFilename, ENT_QUOTES);
        $toolbar = <<<HTML
            <a class="btn btn-xs btn-default" href="{$csvUri}" download="{$filenameEsc}">
                <i class="fa fa-download"></i> Export CSV
            </a>
            HTML;
    }

    return debug_panel($title, $body, $toolbar);
}

/**
 * Return copy-to-clipboard + download buttons for the element identified by $textId.
 *
 * Copy reads textContent from the element at click time.
 * Download builds a Blob from the same text and triggers a save-as dialog.
 *
 * @param  string  $textId  id of the <pre> or element containing the text to copy/export
 * @param  string  $filename  Default filename for the download dialog
 * @param  string  $mimeType  MIME type for the download Blob (default application/json)
 */
function debug_toolbar(string $textId, string $filename, string $mimeType = 'application/json'): string
{
    $filenameEsc = htmlspecialchars($filename, ENT_QUOTES);
    $mimeTypeEsc = htmlspecialchars($mimeType, ENT_QUOTES);
    $textIdEsc = htmlspecialchars($textId, ENT_QUOTES);

    return <<<HTML
        <button type="button" class="btn btn-xs btn-default"
                onclick="(function(){
                    var t = document.getElementById('{$textIdEsc}');
                    var s = t ? t.textContent : '';
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(s);
                    } else {
                        var ta = document.createElement('textarea');
                        ta.value = s;
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        document.body.removeChild(ta);
                    }
                })()">
            <i class="fa fa-copy"></i> Copy
        </button>
        <button type="button" class="btn btn-xs btn-default"
                onclick="(function(){
                    var t = document.getElementById('{$textIdEsc}');
                    var s = t ? t.textContent : '';
                    var b = new Blob([s], {type: '{$mimeTypeEsc}'});
                    var a = document.createElement('a');
                    a.href = URL.createObjectURL(b);
                    a.download = '{$filenameEsc}';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(a.href);
                })()">
            <i class="fa fa-download"></i> Export
        </button>
        HTML;
}
