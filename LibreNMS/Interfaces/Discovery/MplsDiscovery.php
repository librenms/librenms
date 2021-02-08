<?php
/**
 * MplsDiscovery.php
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

namespace LibreNMS\Interfaces\Discovery;

use Illuminate\Support\Collection;

interface MplsDiscovery
{
    /**
     * @return Collection MplsLsp objects
     */
    public function discoverMplsLsps();

    /**
     * @param Collection $lsps collecton of synchronized lsp objects from discoverMplsLsps()
     * @return Collection MplsLspPath objects
     */
    public function discoverMplsPaths($lsps);

    /**
     * @return Collection MplsSdp objects
     */
    public function discoverMplsSdps();

    /**
     * @return Collection MplsService objects
     */
    public function discoverMplsServices();

    /**
     * @param Collection $svcs collecton of synchronized lsp objects from discoverMplsServices()
     * @return Collection MplsSap objects
     */
    public function discoverMplsSaps($svcs);

    /**
     * @param Collection $sdps collecton of synchronized sdp objects from discoverMplsSdps()
     * @param Collection $svcs collecton of synchronized service objects from discoverMplsServices()
     * @return Collection MplsSdpBind objects
     */
    public function discoverMplsSdpBinds($sdps, $svcs);

    /**
     * @return Collection MplsTunnelArHop objects
     */
    public function discoverMplsTunnelArHops($paths);

    /**
     * @return Collection MplsTunnelCHop objects
     */
    public function discoverMplsTunnelCHops($paths);
}
