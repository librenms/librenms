<?php

namespace LibreNMS\DB\Schema\Adapters;

use Illuminate\Database\Connection;

class AdapterFactory
{
    public static function create(Connection $db): SchemaAdapter
    {
        $driver = $db->getDriverName();

        if ($driver === 'mysql') {
            return $db->isMaria() ? new MariaDbAdapter($db) : new MySqlAdapter($db);
        }

        return match ($driver) {
            'sqlite' => new SqliteAdapter($db),
            default => new class($db) extends BaseAdapter {
                protected function getSchemaName(): string
                {
                    return $this->db->getDatabaseName();
                }
            },
        };
    }
}
