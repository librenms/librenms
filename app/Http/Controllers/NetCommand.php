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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use Config;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class NetCommand extends Controller
{


    public function run(Request $request)
    {
        $this->validate($request, [
            'cmd' => 'in:whois,ping,tracert,nmap',
            'query' => 'ip_or_hostname',
        ]);

        ini_set('allow_url_fopen', 0);

        // return both stdout and stderr
        $callback = function ($type, $buffer) {
            return $buffer;
        };

        switch ($request->get('cmd')) {
            case 'whois':
                $cmd = [Config::get('whois', 'whois'), $request->get('query')];
                $callback = function ($type, $buffer) {
                    return preg_replace('/^%.*$/m', '', $buffer);
                };
                break;
            case 'ping':
                $cmd = [Config::get('ping', 'ping'), '-c', '5', $request->get('query')];
                break;
            case 'tracert':
                $cmd = [Config::get('mtr', 'mtr'), '-r', '-c', '5', $request->get('query')];
                break;
            case 'nmap':
                if (!$request->user()->isAdmin()) {
                    return response()->json(['status' => 'error', 'message' => 'Insufficient privileges']);
                } else {
                    $cmd = [Config::get('nmap', 'nmap'), $request->get('query')];
                }
                break;
            default:
                return response()->json(['status' => 'error', 'message' => 'Invalid command']);
        }

        $proc = new Process($cmd);
        $proc->run($callback);

        return response()->json([
            'status' => 'ok',
            'output' => htmlentities(trim($proc->getOutput()), ENT_QUOTES),
        ]);
    }
}
