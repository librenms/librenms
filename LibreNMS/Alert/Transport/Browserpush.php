<?php
/*
 * BrowserPush.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Alert\Transport;

use App\Models\User;
use App\Notifications\AlertNotification;
use LibreNMS\Alert\Transport;
use Notification;

class Browserpush extends Transport
{
    protected $name = 'Browser Push';

    public function deliverAlert($alert_data, $opts)
    {
        $users = User::when($this->config['user'] ?? 0, function ($query, $user_id) {
            return $query->where('user_id', $user_id);
        })->get();

        Notification::send($users, new AlertNotification(
            $alert_data['alert_id'],
            $alert_data['title'],
            $alert_data['msg'],
        ));

        return true;
    }

    public static function configTemplate()
    {
        $users = [__('All Users') => 0];
        foreach (User::get(['user_id', 'username', 'realname']) as $user) {
            $users[$user->realname ?: $user->username] = $user->user_id;
        }

        return [
            'config' => [
                [
                    'title' => 'User',
                    'name' => 'user',
                    'descr' => 'LibreNMS User',
                    'type' => 'select',
                    'options' => $users,
                ],
            ],
            'validation' => [
                'user' => 'required|zero_or_exists:users,user_id',
            ],
        ];
    }

    public function displayDetails(): string
    {
        if ($this->config['user'] == 0) {
            $count = \DB::table('push_subscriptions')->count();

            return "All users: $count subscriptions";
        } elseif ($user = User::find($this->config['user'])) {
            $count = $user->pushSubscriptions()->count();

            return "User: $user->username ($count subscriptions)";
        }

        return 'User not found';
    }
}
