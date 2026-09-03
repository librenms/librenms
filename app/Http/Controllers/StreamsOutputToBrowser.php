<?php
/**
 * StreamingController.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App\Logging\NoColorFormatter;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

trait StreamsOutputToBrowser
{
    protected function headers(?string $downloadFile = null): array
    {
        $headers = [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/plain',
        ];

        if ($downloadFile) {
            $headers += [
                'Content-Description' => 'File Transfer',
                'Content-Disposition' => 'attachment; filename=' . $downloadFile,
                'Content-Transfer-Encoding' => 'binary',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
                'Pragma' => 'public',
            ];
        }

        return $headers;
    }

    protected function configureLoggerToStreamOutput(): OutputStyle
    {
        config(['logging.channels.stream' => [
            'driver' => 'custom',
            'via' => fn (array $config): Logger => new Logger(
                $config['name'] ?? 'stream',
                [(new StreamHandler('php://output', $config['level'] ?? Level::Debug))->setFormatter(new NoColorFormatter())]
            ),
            'level' => 'debug',
        ]]);
        Log::setDefaultDriver('stream');

        return new OutputStyle(
            new ArrayInput([]),
            new StreamOutput(fopen('php://output', 'w'), decorated: false)
        );
    }
}
