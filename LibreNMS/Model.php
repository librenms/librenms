<?php

namespace LibreNMS;

abstract class Model
{
    protected static $table;
    protected static $primaryKey = 'id';

    public static function create(array $data)
    {
        $instance = new static();
        $instance->fill($data);

        return $instance;
    }

    protected function fill(array $data = [])
    {
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }
    }

    /**
     * Save Models and remove invalid Models
     * This the sensors array should contain all the sensors of a specific class
     * It may contain sensors from multiple tables and devices, but that isn't the primary use
     *
     * @param int $device_id
     * @param array $models
     * @param array $unique_fields fields to search for an existing entry
     * @param array $ignored_update_fields Don't compare these field when updating
     */
    final public static function sync($device_id, array $models, $unique_fields = [], $ignored_update_fields = [])
    {
        // save and collect valid ids
        $valid_ids = [];
        foreach ($models as $model) {
            /** @var $this $model */
            if ($model->isValid()) {
                $valid_ids[] = $model->save($unique_fields, $ignored_update_fields);
            }
        }

        // delete invalid sensors
        self::clean($device_id, $valid_ids);
    }

    /**
     * Remove invalid Models.  Passing an empty array will remove all models related to $device_id
     *
     * @param int $device_id
     * @param array $model_ids valid Model ids
     */
    protected static function clean($device_id, $model_ids)
    {
        $table = static::$table;
        $key = static::$primaryKey;

        $params = [$device_id];
        $where = '`device_id`=?';

        if (! empty($model_ids)) {
            $where .= " AND `$key` NOT IN " . dbGenPlaceholders(count($model_ids));
            $params = array_merge($params, $model_ids);
        }

        $rows = dbFetchRows("SELECT * FROM `$table` WHERE $where", $params);
        foreach ($rows as $row) {
            static::onDelete(static::create($row));
        }
        if (! empty($rows)) {
            dbDelete($table, $where, $params);
        }
    }

    /**
     * Save this Model to the database.
     *
     * @param array $unique_fields fields to search for an existing entry
     * @param array $ignored_update_fields Don't compare these field when updating
     * @return int the id of this model in the database
     */
    final public function save($unique_fields = [], $ignored_update_fields = [])
    {
        $db_model = $this->fetch($unique_fields);
        $key = static::$primaryKey;

        if ($db_model) {
            $new_model = $this->toArray(array_merge([$key], $ignored_update_fields));
            $update = array_diff($new_model, $db_model);

            if (empty($update)) {
                static::onNoUpdate();
            } else {
                dbUpdate($update, static::$table, "`$key`=?", [$this->$key]);
                static::onUpdate($this);
            }
        } else {
            $new_model = $this->toArray([$key]);
            $this->$key = dbInsert($new_model, static::$table);
            if ($this->$key !== null) {
                static::onCreate($this);
            }
        }

        return $this->$key;
    }

    /**
     * Fetch the sensor from the database.
     * If it doesn't exist, returns null.
     *
     * @param array $unique_fields fields to search for an existing entry
     * @return array|null
     */
    protected function fetch($unique_fields = [])
    {
        $table = static::$table;
        $key = static::$primaryKey;

        if (isset($this->id)) {
            return dbFetchRow(
                "SELECT `$table` FROM ? WHERE `$key`=?",
                [$this->$key]
            );
        }

        $where = [];
        $params = [];
        foreach ($unique_fields as $field) {
            if (isset($this->$field)) {
                $where[] = " $field=?";
                $params[] = $this->$field;
            }
        }

        if (empty($params)) {
            return null;
        }

        $row = dbFetchRow(
            "SELECT * FROM `$table` WHERE " . implode(' AND', $where),
            $params
        );

        $this->$key = $row[$key];

        return $row;
    }

    /**
     * Convert this Model to an array with fields that match the database
     *
     * @param array $exclude Exclude the listed fields
     * @return array
     */
    abstract public function toArray($exclude = []);

    /**
     * Returns if this model passes validation and should be saved to the database
     *
     * @return bool
     */
    abstract public function isValid();

    /**
     * @param static $model
     */
    public static function onDelete($model)
    {
        if (\App::runningInConsole()) {
            echo '-';
        }
    }

    /**
     * @param static $model
     */
    public static function onCreate($model)
    {
        if (\App::runningInConsole()) {
            echo '+';
        }
    }

    /**
     * @param static $model
     */
    public static function onUpdate($model)
    {
        if (\App::runningInConsole()) {
            echo 'U';
        }
    }

    public static function onNoUpdate()
    {
        if (\App::runningInConsole()) {
            echo '.';
        }
    }
}
