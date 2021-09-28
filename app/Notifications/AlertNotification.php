<?php
/*
 * AlertNotification.php
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

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class AlertNotification extends Notification
{
    /**
     * @var \NotificationChannels\WebPush\WebPushMessage
     */
    public $message;

    public function __construct(int $alert_id, string $title, string $body)
    {
        $this->message = (new WebPushMessage)
            ->title($title)
            ->icon(asset('/images/mstile-144x144.png'))
            ->body($body)
            ->action('Acknowledge', 'alert.acknowledge')
            ->action('View', 'alert.view')
            ->options(['TTL' => 2000])
            ->data(['id' => $alert_id])
            // ->badge()
            // ->dir()
            // ->image()
            // ->lang()
            // ->renotify()
            // ->requireInteraction()
            // ->tag()
            // ->vibrate()
;
    }

    /**
     * @param  mixed  $notifiable
     * @return string[]
     */
    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \NotificationChannels\WebPush\WebPushMessage
     */
    public function toWebPush($notifiable, Notification $notification): WebPushMessage
    {
        return $this->message;
    }
}
