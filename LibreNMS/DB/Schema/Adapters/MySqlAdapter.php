<?php

namespace LibreNMS\DB\Schema\Adapters;

class MySqlAdapter extends AbstractMySqlAdapter
{
    public function mapColumn(array $col, array $tableExtras): array
    {
        $def = parent::mapColumn($col, $tableExtras);
        $extra = (string) ($tableExtras[$col['name']] ?? '');
        $def['Extra'] = preg_replace('/DEFAULT_GENERATED[ ]*/', '', $extra);

        return $def;
    }
}
