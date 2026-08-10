<?php

namespace LibreNMS\DB\Schema\Adapters;

class MySqlAdapter extends AbstractMySqlAdapter
{
    /**
     * @param  array<string, mixed>  $col
     * @param  array<string, mixed>  $tableExtras
     * @return array{Field: string, Type: string, Null: bool, Default?: mixed, Extra: string}
     */
    public function mapColumn(array $col, array $tableExtras): array
    {
        $def = parent::mapColumn($col, $tableExtras);
        $extra = (string) ($tableExtras[$col['name']] ?? '');
        $def['Extra'] = preg_replace('/DEFAULT_GENERATED[ ]*/', '', $extra);

        return $def;
    }
}
