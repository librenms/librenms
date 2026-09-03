<?php

/**
 * StreamsOutputToBrowser.php
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

namespace App\Http\Controllers\Traits;

use App\Logging\CliColorFormatter;
use App\Logging\FlushHandler;
use App\Logging\NoColorFormatter;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait StreamsOutputToBrowser
{
    private bool $bufferedOutput = false;
    private bool $colour = false;
    private ?string $downloadFile = null;

    protected function stream(callable $function): StreamedResponse
    {
        return new StreamedResponse($function, 200, $this->headers());
    }

    protected function enableBufferedOutput(): void
    {
        $this->bufferedOutput = true;
    }

    protected function enableColour(): void
    {
        $this->colour = true;
    }

    protected function enableDownload(string $downloadFile): void
    {
        $this->downloadFile = $downloadFile;
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        $headers = [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/plain',
        ];

        if ($this->downloadFile) {
            $headers += [
                'Content-Description' => 'File Transfer',
                'Content-Disposition' => 'attachment; filename=' . $this->downloadFile,
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
        $formatter = $this->colour ? new CliColorFormatter() : new NoColorFormatter();
        $handlers = [(new StreamHandler('php://output', Level::Debug))->setFormatter($formatter)];
        if (! $this->bufferedOutput) {
            $handlers[] = new FlushHandler();
        }
        config(['logging.channels.stream' => [
            'driver' => 'custom',
            'via' => fn (): Logger => new Logger('stream', $handlers),
            'level' => 'debug',
        ]]);
        Log::setDefaultDriver('stream');

        return new OutputStyle(
            new ArrayInput([]),
            new StreamOutput(fopen('php://output', 'w'), decorated: false)
        );
    }
}
