<?php
/**
 * AdldapImportScope.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Authentication;

use Adldap\Laravel\Scopes\ScopeInterface;
use Adldap\Query\Builder;
use Illuminate\Support\Str;
use LibreNMS\Config;

class AdldapImportScope implements ScopeInterface
{
    /**
     * If group membership is required, check that user is in one of the given groups
     *
     * @param Builder $builder
     *
     * @return void
     */
    public function apply(Builder $builder)
    {
        if (Config::get('auth_ad_require_groupmembership')) {
            $groups = collect(Config::get('auth_ad_groups'))
                ->keys()
                ->filter(function ($group) {
                    return Str::startsWith(strtolower($group), 'cn=');
            });

            if (!empty($groups)) {
                $builder->andFilter(function (Builder $builder) use ($groups) {
                    foreach ($groups as $group) {
                        $builder->orWhereMemberOf($group);
                    }
                });
            } else {
                $builder->where('dn', '=', 0); // find nothing
            }
        }
    }
}
