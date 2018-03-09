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

$path_exec = shell_exec("which composer 2> /dev/null");
if (!empty($path_exec)) {
    $exec = trim($path_exec);
} elseif (is_file($install_dir . '/composer.phar')) {
    $exec = 'php ' . $install_dir . '/composer.phar';
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

    // Download composer.phar (code from the composer web site)
    $good_sha = trim(@file_get_contents(($use_https ? 'https' : 'http') . '://composer.github.io/installer.sig'));

    // Download composer.phar (code from the composer web site)
    @copy(($use_https ? 'https' : 'http') . '://getcomposer.org/installer', 'composer-setup.php');
    if (!empty($good_sha) && @hash_file('SHA384', 'composer-setup.php') === $good_sha) {
        // Installer verified
        shell_exec('php composer-setup.php');
        $exec = 'php ' . $install_dir . '/composer.phar';
    } else {
        echo "Corrupted download.\n";
    }
    @unlink('composer-setup.php');
}

if ($exec) {
    passthru("$exec " . implode(' ', array_splice($argv, 1)) . ' 2>&1');
} else {
    echo "Composer not available, please manually install composer.\n";
}
