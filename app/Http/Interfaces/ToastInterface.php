<?php
/**
 * ToastInterface.php
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
 * @copyright  2024 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Interfaces;

use Illuminate\Session\SessionManager;

class ToastInterface
{
    public function __construct(
        private SessionManager $session
    ) {
    }

    public function info(string $title, ?string $message = null, ?array $options = null): static
    {
        return $this->message('info', $title, $message, $options);
    }

    public function success(string $title, ?string $message = null, ?array $options = null): static
    {
        return $this->message('success', $title, $message, $options);
    }

    public function error(string $title, ?string $message = null, ?array $options = null): static
    {
        return $this->message('error', $title, $message, $options);
    }

    public function warning(string $title, ?string $message = null, ?array $options = null): static
    {
        return $this->message('warning', $title, $message, $options);
    }

    public function message(string $level, string $title, ?string $message = null, ?array $options = null): static
    {
        $notifications = $this->session->get('toasts', []);
        array_push($notifications, [
            'level' => $level,
            'title' => $message === null ? '' : $title,
            'message' => $message ?? $title,
            'options' => $options ?? [],
        ]);
        $this->session->flash('toasts', $notifications);

        return $this;
    }
}
