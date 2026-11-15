<?php

/**
 * ComposerHelper.php
 *
 * Helper functions for composer
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use Composer\Script\Event;
use LibreNMS\Exceptions\FileWriteFailedException;
use LibreNMS\Util\EnvHelper;
use Minishlink\WebPush\VAPID;

class ComposerHelper
{
    public static function postRootPackageInstall(Event $event): void
    {
        if (! file_exists('.env')) {
            self::setPermissions();
            self::populateEnv();
        }
    }

    public static function postInstall(Event $event): void
    {
        if (! file_exists('.env')) {
            self::setPermissions();
        }

        self::populateEnv();
    }

    public static function preUpdate(Event $event): void
    {
        if (! getenv('FORCE')) {
            echo "Running composer update is not advisable.  Please run composer install to update instead.\n";
            echo "If know what you are doing and want to write a new composer.lock file set FORCE=1.\n";
            echo "If you don't know what to do, run: composer install\n";
            exit(1);
        }
    }

    public static function preInstall(Event $event)
    {
    }

    public static function addPlugin(string $package, ?string $version = null): int
    {
        return self::execComposerCommand(
            ['require', '--no-update', $package . ($version ? ":$version" : '')],
            ['COMPOSER' => self::getPluginsFile()],
        );
    }

    public static function addPackage(string $package, ?string $version = null): int
    {
        self::prepareComposerFile();

        return self::execComposerCommand(
            ['require', '--update-no-dev', $package . ($version ? ":$version" : '')],
            ['FORCE' => 1],
        );
    }

    public static function removePlugin(string $package): int
    {
        return self::execComposerCommand(
            ['remove', '--no-update', $package],
            ['COMPOSER' => self::getPluginsFile()],
        );
    }

    public static function removePackage(string $package): int
    {
        self::prepareComposerFile();

        return self::execComposerCommand(
            ['remove', '--update-no-dev', $package],
            ['FORCE' => 1],
        );
    }

    /**
     * Install all packages listed in the plugins file
     */
    public static function installPlugins(): int
    {
        $packages = [];
        foreach (self::getPlugins() as $package => $version) {
            $packages[] = "$package:$version";
        }

        if (empty($packages)) {
            return 0;
        }

        self::prepareComposerFile();

        return self::execComposerCommand(
            ['require', '--update-no-dev', ...$packages],
            ['FORCE' => 1],
        );
    }

    public static function getPlugins(): array
    {
        $file = self::getPluginsFile();

        $plugins = is_file($file) && is_readable($file) ?
            json_decode((string) file_get_contents($file), true) : [];

        return $plugins['require'] ?? [];
    }

    /**
     * The file that records the packages added by plugin:add
     */
    public static function getPluginsFile(): string
    {
        // Not cwd-relative: also reached from the web UI, where cwd is the
        // document root. Not base_path(): this class runs as a composer script.
        return getenv('LIBRENMS_COMPOSER_PLUGINS') ?: self::installDir() . '/composer.plugins.json';
    }

    /**
     * When composer is pointed at another composer.json with the COMPOSER
     * environment variable, start it from the one LibreNMS ships.
     */
    private static function prepareComposerFile(): void
    {
        $file = getenv('COMPOSER');
        if (! $file || is_file($file)) {
            return;
        }

        $dir = dirname($file);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        copy(self::installDir() . '/composer.json', $file);

        $lock = pathinfo($file, PATHINFO_EXTENSION) === 'json' ? substr($file, 0, -4) . 'lock' : $file . '.lock';
        copy(self::installDir() . '/composer.lock', $lock);
    }

    private static function installDir(): string
    {
        return (string) realpath(__DIR__ . '/..');
    }

    /**
     * Initially populate .env file
     */
    private static function populateEnv(): void
    {
        $config = [
            'db_host' => '',
            'db_port' => '',
            'db_name' => '',
            'db_user' => '',
            'db_pass' => '',
            'db_socket' => '',
            'base_url' => '',
            'user' => '',
            'group' => '',
        ];

        $config_file = realpath('config.php');
        if ($config_file !== false) {
            @include $config_file;
        }

        try {
            EnvHelper::init();
            $vapid = VAPID::createVapidKeys();

            EnvHelper::writeEnv([
                'NODE_ID' => uniqid(),
                'DB_HOST' => $config['db_host'],
                'DB_PORT' => $config['db_port'],
                'DB_USERNAME' => $config['db_user'],
                'DB_PASSWORD' => $config['db_pass'],
                'DB_DATABASE' => $config['db_name'],
                'DB_SOCKET' => $config['db_socket'],
                'APP_URL' => $config['base_url'],
                'LIBRENMS_USER' => $config['user'],
                'LIBRENMS_GROUP' => $config['group'],
                'VAPID_PUBLIC_KEY' => $vapid['publicKey'],
                'VAPID_PRIVATE_KEY' => $vapid['privateKey'],
            ]);
        } catch (FileWriteFailedException $exception) {
            echo $exception->getMessage() . PHP_EOL;
        }
    }

    private static function setPermissions(): void
    {
        self::exec([
            'cd ' . realpath(__DIR__ . '/../..'),
            'setfacl -R -m g::rwx rrd/ logs/ storage/ bootstrap/cache/',
            'setfacl -d -m g::rwx rrd/ logs/ storage/ bootstrap/cache/',
        ]);
    }

    /**
     * Run a command or array of commands and echo the command and output
     *
     * @param  string[]  $cmds
     */
    private static function exec(array $cmds): int
    {
        $cmd = "set -v\n" . implode(PHP_EOL, $cmds);
        passthru($cmd, $result_code);

        return $result_code;
    }

    /**
     * @param  string[]  $command
     * @param  array<string, string|int|float>  $env
     * @return int
     */
    private static function execComposerCommand(array $command, array $env = []): int
    {
        $cli = [];
        foreach ($env as $key => $value) {
            $cli[] = $key . '=' . escapeshellarg((string) $value);
        }
        $cli[] = PHP_BINARY;
        $cli[] = realpath(__DIR__ . '/../scripts/composer_wrapper.php');
        foreach ($command as $word) {
            $cli[] = escapeshellarg($word);
        }

        return self::exec([implode(' ', $cli)]);
    }
}
