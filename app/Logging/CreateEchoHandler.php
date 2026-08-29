<?php

/*
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
 * @copyright  2026 Steven Wilton
 * @author Steven Wilton <swilton@fluentit.au>
 */

namespace App\Logging;

use Monolog\Logger;

class CreateEchoHandler
{
    public function __invoke(array $config): Logger
    {
        // Create a new Monolog instance using the channel config name
        $logger = new Logger($config['name'] ?? 'echo');

        // Instantiate and push your custom handler
        $handler = new EchoHandler();

        // Optional: Set a minimum logging level dynamically from config
        $handler->setLevel($config['level'] ?? \Monolog\Level::Debug);
        $handler->setFormatter(new BrowserColorFormatter());

        $logger->pushHandler($handler);

        return $logger;
    }
}
