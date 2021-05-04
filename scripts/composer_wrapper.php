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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

if (! is_writable(getenv('HOME'))) {
    // set COMPOSER_HOME in case HOME isn't set or writable
    putenv("COMPOSER_HOME=$install_dir/.composer");
}

$use_https = true;
// Set up proxy if needed, check git config for proxies too
if ($proxy = getenv('HTTPS_PROXY') ?: getenv('https_proxy')) {
    $use_https = true;
} elseif ($proxy = getenv('HTTP_PROXY') ?: getenv('http_proxy')) {
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
    $exec = PHP_BINDIR . '/php ' . $install_dir . '/composer.phar';

    // If older than 1 week, try update
    if (time() - filemtime($install_dir . '/composer.phar') > 60 * 60 * 24 * 7) {
        // self-update
        passthru("$exec self-update --quiet --2" . $extra_args);
        touch($install_dir . '/composer.phar');
    }
} else {
    $sig_url = ($use_https ? 'https' : 'http') . '://composer.github.io/installer.sig';

    // Download installer signature from github
    $good_sha = trim(curl_fetch($sig_url, $proxy, $use_https));

    if (empty($good_sha)) {
        echo "Error: Failed to download installer signature from $sig_url\n";
    } else {
        // Download composer.phar (code from the composer web site)
        $dest = 'composer-setup.php';
        $installer_url = ($use_https ? 'https' : 'http') . '://getcomposer.org/installer';
        curl_fetch($installer_url, $proxy, $use_https, $dest);

        if (! is_file($dest)) {
            echo "Error: Failed to download $installer_url\n";
        } elseif (@hash_file('SHA384', $dest) === $good_sha) {
            // Installer verified
            shell_exec(PHP_BINDIR . "/php $dest");
            $exec = PHP_BINDIR . "/php $install_dir/composer.phar";
        } else {
            echo "Error: Corrupted download, signature doesn't match for $installer_url\n";
        }
        @unlink($dest);
    }
}

// if nothing else, use system supplied composer
if (! $exec) {
    $path_exec = trim(shell_exec('which composer 2> /dev/null'));
    if ($path_exec) {
        $exec = $path_exec;
    }
}

if ($exec) {
    passthru("$exec " . implode(' ', array_splice($argv, 1)) . "$extra_args 2>&1", $exit_code);
    exit($exit_code);
} else {
    echo "Composer not available, please manually install composer.\n";
}

function curl_fetch($url, $proxy, $use_https, $output = false)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    if ($output) {
        $fp = fopen($output, 'w+');
        curl_setopt($curl, CURLOPT_FILE, $fp);
    }

    if ($proxy) {
        curl_setopt($curl, CURLOPT_PROXY, rtrim(str_replace(['http://', 'https://'], '', $proxy), '/'));
        if ($use_https) {
            curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 1);
        }
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 300);
    $ret = @curl_exec($curl);

    curl_close($curl);
    if (isset($fp)) {
        fclose($fp);
    }

    return $ret;
}
