<?php

namespace LibreNMS\DB\Schema\Adapters;

class MariaDbAdapter extends AbstractMySqlAdapter
{
    /**
     * @param  array<string, mixed>  $col
     * @param  array<string, mixed>  $tableExtras
     * @return array{Field: string, Type: string, Null: bool, Default?: mixed, Extra: string}
     */
    public function mapColumn(array $col, array $tableExtras): array
    {
        $def = parent::mapColumn($col, $tableExtras);
        $extra = str_replace('current_timestamp()', 'CURRENT_TIMESTAMP', (string) ($tableExtras[$col['name']] ?? ''));
        $def['Extra'] = $extra;

        return $def;
    }
}
