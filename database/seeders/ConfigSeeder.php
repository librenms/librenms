<?php
/*
 * ConfigSeeder.php
 *
 * Imports yaml config settings from the database/seeders/config directory and imports them into the database
 * This way we can have a nice initial config set.  Primarily for docker and other automations.
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

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use LibreNMS\Config;
use Symfony\Component\Yaml\Yaml;

class ConfigSeeder extends Seeder
{
    /**
     * @var string[]
     */
    private $directories;

    public function __construct()
    {
        $this->directories = [dirname(__FILE__) . '/config'];

        if (is_dir('/data/config')) {
            $this->directories[] = '/data/config';
        }
    }

    public function run(): void
    {
        $files = array_merge(...array_map(function ($dir) {
            return glob("$dir/*.y*ml");  // both .yml and .yaml extensions
        }, $this->directories));

        if (empty($files)) {
            return; // nothing to do
        }

        if (\App\Models\Config::exists()) {
            if (! $this->command->confirm(trans('commands.db:seed.existing_config'), false)) {
                return; // don't overwrite existing settings.
            }
        }

        foreach ($files as $file) {
            $settings = Yaml::parse(file_get_contents($file));
            foreach (Arr::wrap($settings) as $key => $value) {
                if (! is_string($key)) {
                    echo 'Skipped non-string config key: ' . json_encode($key) . PHP_EOL;
                    continue;
                }

                Config::persist($key, $value);
            }
        }
    }
}
