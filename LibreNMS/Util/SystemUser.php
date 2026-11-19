<?php

namespace LibreNMS\Util;

/**
 * The operating system user running this process
 */
class SystemUser
{
    /**
     * Name of the effective user, or the uid when it has no passwd entry
     */
    public static function name(): string
    {
        // either can be disabled on its own with disable_functions
        if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
            $uid = posix_geteuid();

            return posix_getpwuid($uid)['name'] ?? (string) $uid;
        }

        // the environment can name another user, so it is only the fallback
        return getenv('USERNAME') ?: getenv('USER') ?: '?';
    }

    /**
     * Check the effective uid, so root without a passwd entry is still root
     */
    public static function isRoot(): bool
    {
        return function_exists('posix_geteuid') ? posix_geteuid() === 0 : self::name() === 'root';
    }
}
