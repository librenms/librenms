<?php
/**
 * NetCommand.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LibreNMS\Config;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

class NetCommand extends Controller
{
    public function run(Request $request)
    {
        $this->validate($request, [
            'cmd' => 'in:whois,ping,tracert,nmap',
            'query' => 'ip_or_hostname',
        ]);

        ini_set('allow_url_fopen', '0');

        switch ($request->get('cmd')) {
            case 'whois':
                $cmd = [Config::get('whois', 'whois'), $request->get('query')];
                break;
            case 'ping':
                $cmd = [Config::get('ping', 'ping'), '-c', '5', $request->get('query')];
                break;
            case 'tracert':
                $cmd = [Config::get('mtr', 'mtr'), '-r', '-c', '5', $request->get('query')];
                break;
            case 'nmap':
                if (! $request->user()->isAdmin()) {
                    return response('Insufficient privileges');
                } else {
                    $cmd = [Config::get('nmap', 'nmap'), $request->get('query')];
                }
                break;
            default:
                return response('Invalid command');
        }

        $proc = new Process($cmd);
        $proc->setTimeout(240);

        //stream output
        return (new StreamedResponse(
            function () use ($proc, $request) {
                // a bit dirty, bust browser initial cache
                $ua = $request->header('User-Agent');
                if (Str::contains($ua, ['Chrome', 'Trident'])) {
                    $char = "\f"; // line feed
                } else {
                    $char = '';
                }
                echo str_repeat($char, 4096);
                echo PHP_EOL; // avoid first line mess ups due to line feed

                $proc->run(function ($type, $buffer) {
                    echo $buffer;
                    ob_flush();
                    flush();
                });
            },
            200,
            [
                'Content-Type' => 'text/plain; charset=utf-8',
                'X-Accel-Buffering' => 'no',
            ]
        ))->send();
    }
}
