<?php
/**
 * MplsPolling.php
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

namespace LibreNMS\Interfaces\Polling;

use Illuminate\Support\Collection;

interface MplsPolling
{
    /**
     * @return Collection MplsLsp objects
     */
    public function pollMplsLsps();

    /**
     * @param Collection $lsps collecton of synchronized lsp objects from pollMplsLsps()
     * @return Collection MplsLspPath objects
     */
    public function pollMplsPaths($lsps);

    /**
     * @return Collection MplsSdp objects
     */
    public function pollMplsSdps();

    /**
     * @return Collection MplsService objects
     */
    public function pollMplsServices();

    /**
     * @param Collection $svcs collecton of synchronized service objects from pollMplsServices()
     * @return Collection MplsSap objects
     */
    public function pollMplsSaps($svcs);

    /**
     * @param Collection $sdps collecton of synchronized sdp objects from pollMplsSdps()
     * @param Collection $svcs collecton of synchronized service objects from pollMplsServices()
     * @return Collection MplsSdpBind objects
     */
    public function pollMplsSdpBinds($sdps, $svcs);

    /**
     * @return Collection MplsTunnelArHop objects
     */
    public function pollMplsTunnelArHops($paths);

    /**
     * @return Collection MplsTunnelCHop objects
     */
    public function pollMplsTunnelCHops($paths);
}
