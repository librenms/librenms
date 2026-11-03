<?php

namespace LibreNMS\DB;

use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;

/** Insert raw rows without Eloquent events or generated model IDs. */
class BulkInsert
{
    /**
     * Keep omitted columns omitted: filling them with NULL would override database defaults.
     * Flush on a change of columns, rather than reordering rows into groups.
     *
     * @param  iterable<array<string, mixed>>  $rows
     * @param  Closure(int): void|null  $inserted  Called only after a successful statement.
     */
    public static function insert(Builder $query, iterable $rows, ?Closure $inserted = null): void
    {
        $grammar = $query->getGrammar();
        $bindingLimit = match (true) {
            $grammar instanceof SQLiteGrammar => 999,
            $grammar instanceof SqlServerGrammar => 2100,
            default => 65535,
        };
        $byteLimit = null;
        $batch = [];
        $columns = null;
        $bytes = 0;
        foreach ($rows as $row) {
            if ($byteLimit === null) {
                $byteLimit = 262144;
                if ($grammar instanceof MySqlGrammar) {
                    $packet = $query->getConnection()->selectOne('SELECT @@max_allowed_packet AS packet_size', [], false);
                    $byteLimit = max(1, min($byteLimit, intdiv((int) $packet->packet_size, 2)));
                }
            }

            ksort($row);
            $keys = array_keys($row);
            // Bound packet size as well as row/binding counts for large string attributes.
            $rowBytes = array_sum(array_map(fn ($value) => is_string($value) ? strlen($value) + 16 : 16, $row));
            if ($keys !== $columns || count($batch) >= 1000 ||
                (count($batch) + 1) * count($row) > $bindingLimit || $bytes + $rowBytes > $byteLimit) {
                self::flush($query, $batch, $inserted);
                $batch = [];
                $bytes = 0;
                $columns = $keys;
            }

            $batch[] = $row;
            $bytes += $rowBytes;
        }

        self::flush($query, $batch, $inserted);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  Closure(int): void|null  $inserted
     */
    private static function flush(Builder $query, array $rows, ?Closure $inserted): void
    {
        if ($rows !== []) {
            $query->insert($rows);
            $inserted?->__invoke(count($rows));
        }
    }
}
