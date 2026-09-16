<?php

declare(strict_types=1);

namespace LibreNMS\Util;

class DataProviderCache
{
    /**
     * Cache test or data provider result in a persistent file cache on disk.
     * Automatically invalidates if the monitored file or directory modification time ($watchPath) is newer than the cache file.
     *
     * @param  string  $key  Unique cache identifier key
     * @param  string  $watchPath  File or directory path to monitor for mtime changes
     * @param  callable(): mixed  $callback
     * @return mixed
     */
    public static function remember(string $key, string $watchPath, callable $callback): mixed
    {
        $basePath = realpath(__DIR__ . '/../..');
        $cacheFile = $basePath . '/storage/framework/cache/test_' . $key . '.php';
        $watchMtime = self::getWatchMtime($watchPath);

        if (file_exists($cacheFile) && filemtime($cacheFile) >= $watchMtime) {
            return require $cacheFile;
        }

        $data = $callback();

        if (! is_dir(dirname($cacheFile))) {
            @mkdir(dirname($cacheFile), 0755, true);
        }

        @file_put_contents($cacheFile, '<?php return ' . var_export($data, true) . ';');

        return $data;
    }

    private static function getWatchMtime(string $watchPath): int
    {
        $mtime = @filemtime($watchPath);
        if ($mtime === false) {
            return PHP_INT_MAX; // Force cache invalidation if filesystem stat fails
        }

        if (is_dir($watchPath)) {
            $files = glob(rtrim($watchPath, '/') . '/*');
            if ($files) {
                foreach ($files as $file) {
                    $fmtime = @filemtime($file);
                    if ($fmtime !== false && $fmtime > $mtime) {
                        $mtime = $fmtime;
                    }
                }
            }
        }

        return $mtime;
    }

    /**
     * Clear all persistent test cache files.
     */
    public static function clear(): void
    {
        $basePath = realpath(__DIR__ . '/../..');
        $dir = $basePath . '/storage/framework/cache';

        if (is_dir($dir)) {
            foreach (glob($dir . '/test_*.php') as $file) {
                @unlink($file);
            }
        }
    }
}
