<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    LibreNMS
 * @subpackage FileLock
 * @copyright  (C) 2017
 *
 */

namespace LibreNMS;

class FileLock
{
    private $name = "";
    private $file = "";
    /**
     * @var resource | false
     */
    private $handle = false;
    private $acquired = false;

    private function __construct($lock_name)
    {
        global $config;

        $this->name = $lock_name;
        $this->file = "$config[install_dir]/.$lock_name.lock";
        $this->handle = fopen($this->file, "w+");
    }

    public function __destruct()
    {
        $this->release();
    }

    /**
     * Release the lock.
     */
    public function release()
    {
        if (!$this->acquired) {
            return;
        }

        if ($this->handle !== false) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
        }
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * Given a lock name, try to acquire the lock.
     * On success return a FileLock object, or on failure return false.
     * @param string $lock_name Name of lock
     * @return mixed
     */
    public static function lock($lock_name)
    {
        $lock = new self($lock_name);
        if ($lock->handle === false) {
            return false;
        }

        if (flock($lock->handle, LOCK_EX | LOCK_NB)) {
            $lock->acquired = true;
            return $lock;
        } else {
            return false;
        }
    }

    /**
     * Given a lock name, try to acquire the lock, exiting on failure.
     * On success return a FileLock object.
     * @param string $lock_name Name of lock
     * @return FileLock
     */
    public static function lockOrDie($lock_name)
    {
        $lock = self::lock($lock_name);

        if ($lock === false) {
            echo "Failed to acquire lock $lock_name, exiting\n";
            exit(1);
        }
        return $lock;
    }
}
