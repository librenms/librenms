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
    private $name;
    private $file;
    /**
     * @var resource | false
     */
    private $handle;

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
     * @param int $timeout Try for this many seconds to see if we can acquire the lock.  Default is no wait. A negative timeout will wait forever.
     * @return self|false
     */
    public static function lock($lock_name, $timeout = 0)
    {
        $lock = new self($lock_name);
        if ($lock->handle === false) {
            return false;
        }

        // try to acquire the lock each second until we reach the timeout, once if timeout is 0, forever if timeout < 0
        for ($i = 0; $i <= $timeout || $timeout < 0; $i++) {
            if (flock($lock->handle, $timeout < 0 ? LOCK_EX : LOCK_EX | LOCK_NB)) {
                $lock->acquired = true;
                return $lock;
            }

            if ($timeout) {
                sleep(1);
            }
        }

        return false;
    }

    /**
     * Given a lock name, try to acquire the lock, exiting on failure.
     * On success return a FileLock object.
     * @param string $lock_name Name of lock
     * @param int $timeout Try for this many seconds to see if we can acquire the lock.  Default is no wait. A negative timeout will wait forever.
     * @return self
     */
    public static function lockOrDie($lock_name, $timeout = 0)
    {
        $lock = self::lock($lock_name, $timeout);

        if ($lock === false) {
            echo "Failed to acquire lock $lock_name, exiting\n";
            exit(1);
        }
        return $lock;
    }
}
