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
    private ?string $downloadFile = null;
    private ?OutputStyle $outputBuffer = null;

    protected function stream(callable $function): StreamedResponse
    {
        return new StreamedResponse(function () use ($function): void {
            $this->setupLogger();

            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            ob_implicit_flush();

            $function();
        }, 200, $this->headers());
    }

    protected function enableDownload(string $downloadFile): void
    {
        $this->downloadFile = $downloadFile;
    }

    protected function getCliStreamOutput(): OutputStyle
    {
        $this->outputBuffer ??= new OutputStyle(
            new ArrayInput([]),
            new StreamOutput(fopen('php://output', 'w'), decorated: false)
        );

        return $this->outputBuffer;
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

    private function setupLogger(): void
    {
        config(['logging.channels.stream' => [
            'driver' => 'custom',
            'via' => fn (): Logger => new Logger('stream', [
                (new StreamHandler('php://output', Level::Debug))->setFormatter(new NoColorFormatter()),
            ]),
            'level' => 'debug',
        ]]);
        Log::setDefaultDriver('stream');
    }
}
