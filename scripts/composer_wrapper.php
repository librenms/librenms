#!/usr/bin/env php
<?php
/**
 * composer_wrapper.php
 *
 * Wrapper for composer to use system provided composer or download and use composer.phar
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

if (!is_writable(getenv('HOME'))) {
    // set COMPOSER_HOME in case HOME isn't set or writable
    putenv("COMPOSER_HOME=$install_dir/.composer");
}

$use_https = true;
// Set up proxy if needed, check git config for proxies too
if ($proxy = getenv("HTTPS_PROXY") ?: getenv("https_proxy")) {
    $use_https = true;
} elseif ($proxy = getenv("HTTP_PROXY") ?: getenv("http_proxy")) {
    $use_https = false;
} elseif ($proxy = trim(shell_exec('git config --global --get https.proxy'))) {
    putenv("HTTPS_PROXY=$proxy");
    $use_https = true;
} elseif ($proxy = trim(shell_exec('git config --global --get http.proxy'))) {
    putenv("HTTP_PROXY=$proxy");
    $use_https = false;
}

$exec = false;

$extra_args = '';
if (php_sapi_name() == 'cli' && isset($_SERVER['TERM'])) {
    // running interactively, set output to ansi
    $extra_args .= ' --ansi';
}

if (is_file($install_dir . '/composer.phar')) {
    $exec = 'php ' . $install_dir . '/composer.phar';

    // self-update
    passthru("$exec self-update -q" . $extra_args);
} else {
    if ($proxy) {
        $stream_default_opts = array(
            ($use_https ? 'https' : 'http') => array(
                'proxy' => str_replace(array('http://', 'https://'), 'tcp://', $proxy),
                'request_fulluri' => true,
            )
        );

        stream_context_set_default($stream_default_opts);
    }

    // Download installer signature from github
    $sig_url = ($use_https ? 'https' : 'http') . '://composer.github.io/installer.sig';
    $good_sha = trim(@file_get_contents($sig_url));

    if (empty($good_sha)) {
        echo "Error: Failed to download installer signature from $sig_url\n";
    } else {
        // Download composer.phar (code from the composer web site)
        $dest = 'composer-setup.php';
        $installer_url = ($use_https ? 'https' : 'http') . '://getcomposer.org/installer';
        @copy($installer_url, $dest);

        if (!is_file($dest)) {
            echo "Error: Failed to download $installer_url\n";
        } elseif (@hash_file('SHA384', $dest) === $good_sha) {
            // Installer verified
            shell_exec("php $dest");
            $exec = "php $install_dir/composer.phar";
        } else {
            echo "Error: Corrupted download, signature doesn't match for $installer_url\n";
        }
        @unlink($dest);
    }
}

// if nothing else, use system supplied composer
if (!$exec) {
    $path_exec = trim(shell_exec("which composer 2> /dev/null"));
    if ($path_exec) {
        $exec = $path_exec;
    }
}

if ($exec) {
    passthru("$exec " . implode(' ', array_splice($argv, 1)) . "$extra_args 2>&1");
} else {
    echo "Composer not available, please manually install composer.\n";
}
