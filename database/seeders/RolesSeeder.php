<?php
/**
 * RolesSeeder.php
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // set abilities for default rules
        Bouncer::allow('admin')->everything();
        Bouncer::allow(Bouncer::role()->firstOrCreate(['name' => 'global-read'], ['title' => 'Global Read']))
            ->to('viewAny', '*', []);
        Bouncer::role()->firstOrCreate(['name' => 'user'], ['title' => 'User']);
    }
}
