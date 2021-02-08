<?php
/**
 * TranslationGenerateCommand.php
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

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use Symfony\Component\Process\Process;

class TranslationGenerateCommand extends LnmsCommand
{
    protected $name = 'translation:generate';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Artisan::call('vue-i18n:generate', ['--multi-locales' => 'true', '--format' => 'umd']);

        $npm = new Process(['npm', 'run', 'production']);
        $npm->run();

        if ($npm->getExitCode() !== 0) {
            // npm failed, update hashes manually
            $this->updateManifest();
        }

        return 0;
    }

    private function updateManifest()
    {
        $manifest_file = public_path('mix-manifest.json');
        $manifest = json_decode(file_get_contents($manifest_file), true);
        foreach (glob(public_path('js/lang/*.js')) as $file) {
            $file_name = str_replace(public_path(), '', $file);
            $manifest[$file_name] = $file_name . '?id=' . substr(md5(file_get_contents($file)), 0, 20);
        }

        file_put_contents($manifest_file, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}
