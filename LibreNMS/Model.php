<?php

namespace LibreNMS;

abstract class Model
{
    protected static $table;
    protected static $primaryKey = 'id';

    public function __construct(array $data = array())
    {
        foreach ($data as $field => $value) {
            $this->$field = $value;
        }
    }

    /**
     * Save processors and remove invalid processors
     * This the sensors array should contain all the sensors of a specific class
     * It may contain sensors from multiple tables and devices, but that isn't the primary use
     *
     * @param int $device_id
     * @param array $models
     */
    final public static function sync($device_id, array $models)
    {
        // save and collect valid ids
        $valid_ids = array();
        foreach ($models as $model) {
            /** @var $this $model */
            if ($model->isValid()) {
                $valid_ids[] = $model->save();
            }
        }

        // delete invalid sensors
        self::clean($device_id, $valid_ids);
    }

    /**
     * Remove invalid processors.  Passing an empty array will remove all processors
     *
     * @param int $device_id
     * @param array $model_ids valid processor ids
     */
    protected static function clean($device_id, $model_ids)
    {
        $table = static::$table;
        $params = array($device_id);
        $where = '`device_id`=?';

        if (!empty($model_ids)) {
            $where .= ' AND `processor_id` NOT IN ' . dbGenPlaceholders(count($model_ids));
            $params = array_merge($params, $model_ids);
        }

        $delete = dbFetchRows("SELECT * FROM `$table` WHERE $where", $params);
        foreach ($delete as $processor) {
            static::onDelete(new static($processor));
        }
        if (!empty($delete)) {
            dbDelete($table, $where, $params);
        }
    }

    /**
     * Save this processor to the database.
     *
     * @param array $ignored_update_fields Don't compare these field when updating
     * @return int the id of this model in the database
     */
    final public function save($ignored_update_fields = array() )
    {
        $db_proc = $this->fetch();
        $key = static::$primaryKey;

        if ($db_proc) {
            $new_proc = $this->toArray(array($key, $ignored_update_fields));
            $update = array_diff($new_proc, $db_proc);

            if (empty($update)) {
                static::onNoUpdate();
            } else {
                dbUpdate($update, static::$table, "`$key=?", array($this->$key));
                static::onUpdate($this);
            }
        } else {
            $new_proc = $this->toArray(array($key));
            $this->id = dbInsert($new_proc, static::$table);
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
    protected function fetch($unique_fields = array())
    {
        $table = static::$table;
        $key = static::$primaryKey;

        if (isset($this->id)) {
            return dbFetchRow(
                "SELECT `$table` FROM ? WHERE `$key`=?",
                array($this->$key)
            );
        }

        $where = array();
        $params = array();
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
            "SELECT * FROM `$table` WHERE " .
            implode(' AND', $where),
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
    abstract function toArray($exclude = array());

    /**
     * Returns if this model passes validation and should be saved to the database
     *
     * @return bool
     */
    abstract function isValid();

    /**
     * @param static $model
     */
    public static function onDelete($model) {
        if (isCli()) {
            echo '-';
        }
    }

    /**
     * @param static $model
     */
    public static function onCreate($model) {
        if (isCli()) {
            echo '+';
        }
    }

    /**
     * @param static $model
     */
    public static function onUpdate($model) {
        if (isCli()) {
            echo 'U';
        }
    }

    public static function onNoUpdate() {
        if (isCli()) {
            echo '.';
        }
    }
}
