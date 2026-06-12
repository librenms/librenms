<?php

/**
 * WebServer.php
 *
 * -Description-
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations;

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Str;
use LibreNMS\Validator;

class WebServer extends BaseValidation
{
    /** @var string */
    private $http_regex = '#(http://([^:/]+|\[[a-fA-F\d:]+:[a-fA-F\d:]+]))(?::80)?/#';
    /** @var string */
    private $https_regex = '#(https://([^:/]+|\[[a-fA-F\d:]+:[a-fA-F\d:]+]))(?::443)?/#';
    /** @var string */
    private $host_regex = '#://([^/:\[]+|\[[a-fA-F\d:]+:[a-fA-F\d:]+])#';

    /**
     * @inheritDoc
     */
    public function validate(Validator $validator): void
    {
        if (! app()->runningInConsole()) {
            $url = $this->removeStandardPorts(request()->url());
            $correct_base = str_replace('/validate/results/webserver', '', $url);
            $app_url = rtrim((string) \config('app.url'), '/');
            $expected = $this->removeStandardPorts($app_url . '/validate/results/webserver');

            if ($url !== $expected) {
                preg_match($this->host_regex, $url, $actual_host_match);
                preg_match($this->host_regex, $expected, $expected_host_match);
                $actual_host = $actual_host_match[1] ?? '';
                $expected_host = $expected_host_match[1] ?? "parse failure ($expected)";

                if ($actual_host !== $expected_host) {
                    $nginx = Str::startsWith(request()->server->get('SERVER_SOFTWARE'), 'nginx');
                    $server_name = $nginx ? 'server_name' : 'ServerName';
                    $fix = $nginx ? "server_name $actual_host;" : "ServerName $actual_host";
                    $validator->fail("$server_name is set incorrectly for your webserver, update your webserver config. $actual_host $expected_host", $fix);
                } else {
                    $validator->fail("APP_URL is not set correctly, it should be $correct_base", "Set APP_URL=$correct_base in .env and run: lnms config:cache");
                }
            }

            if (LibrenmsConfig::get('base_url')) {
                $legacy = rtrim((string) LibrenmsConfig::get('base_url'), '/');
                $validator->warn("base_url is deprecated and ignored. Remove it from config.php and set APP_URL=$legacy in .env, then run: lnms config:cache");
            }

            if (request()->secure() && ! \config('session.secure')) {
                $validator->fail('Secure session cookies are not enabled', 'Set SESSION_SECURE_COOKIE=true and run lnms config:cache');
            }
        }
    }

    public function isDefault(): bool
    {
        return ! app()->runningInConsole();
    }

    private function removeStandardPorts(string $url): string
    {
        return preg_replace($this->http_regex, '$1/', (string) preg_replace($this->https_regex, '$1/', $url));
    }
}
