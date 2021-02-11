<?php
/*
 * BaseGraph.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Graphing;

use App\Graphing\Renderer\RendererFactory;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

abstract class BaseGraph extends Controller
{
    public $group;
    public $name;

    /**
     * @var \Carbon\CarbonImmutable
     */
    protected $now;
    /**
     * @var \Carbon\CarbonImmutable
     */
    protected $end;
    /**
     * @var \Carbon\CarbonImmutable
     */
    protected $start;
    /**
     * @var \App\Graphing\Interfaces\Renderer
     */
    protected $renderer;

    protected function init(Request $request)
    {
        $this->now = CarbonImmutable::now();
        $start = $request->get('from', $this->now->subHours(2));
        $this->start = CarbonImmutable::parse(is_numeric($start) ? intval($start) : $start);
        $end = $request->get('to', $this->now);
        $this->end = CarbonImmutable::parse(is_numeric($end) ? intval($end) : $end);

        $this->renderer = RendererFactory::make($request->get('renderer'));
    }

    public static function __set_state(array $properties)
    {
        $class = new static();

        // hardcoded vars for route
        $class->group = $properties['group'];
        $class->name = $properties['name'];

        return $class;
    }
}
