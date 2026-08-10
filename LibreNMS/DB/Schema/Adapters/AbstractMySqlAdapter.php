<?php

namespace LibreNMS\DB\Schema\Adapters;

abstract class AbstractMySqlAdapter extends BaseAdapter
{
    public function getSchemaName(): string
    {
        return $this->db->getDatabaseName();
    }

    public function setSessionState(): void
    {
        $this->db->statement("SET TIME_ZONE='+00:00'");
    }

    public function getPreSql(array $queries): array
    {
        if (preg_grep('/\d{4}-\d\d-\d\d \d\d:\d\d:\d\d/', $queries)) {
            return ["SET TIME_ZONE='+00:00';"];
        }

        return [];
    }

    public function fetchExtras(array $tables): array
    {
        if (empty($tables)) {
            return [];
        }

        $names = array_column($tables, 'name');
        $placeholders = implode(',', array_fill(0, count($names), '?'));
        $rows = $this->db->select(
            "SELECT TABLE_NAME, COLUMN_NAME, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME IN ($placeholders)",
            array_merge([$this->getSchemaName()], $names)
        );

        $extras = [];
        foreach ($rows as $r) {
            $extras[$r->TABLE_NAME][$r->COLUMN_NAME] = $r->EXTRA;
        }

        return $extras;
    }

    public function mapColumn(array $col, array $tableExtras): array
    {
        $def = parent::mapColumn($col, $tableExtras);
        // MySQL/MariaDB often include display width in types, e.g., int(11)
        $def['Type'] = preg_replace('/int\([0-9]+\)/', 'int', (string) $def['Type']);

        return $def;
    }

    public function columnsMatch(array $master, array $current): bool
    {
        $typeMatch = ($master['Type'] === $current['Type']) ||
            ($master['Type'] === 'json' && in_array($current['Type'], ['json', 'longtext', 'text']));

        return $typeMatch &&
            $master['Null'] == $current['Null'] &&
            ($master['Default'] ?? null) == ($current['Default'] ?? null) &&
            $master['Extra'] == $current['Extra'];
    }
}
