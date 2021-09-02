<?php
/**
 * ObjectCache.php
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
 * @copyright  2015 Daniel Preussker <f0o@devilcode.org>
 * @copyright  2015 LibreNMS
 * @author     Daniel Preussker (f0o) <f0o@devilcode.org>
 */

namespace LibreNMS;

use ArrayAccess;

class ObjectCache implements ArrayAccess
{
    private $data = [];

    private $obj = '';

    /**
     * Initialize ObjectCache
     * @param string $obj Name of Object
     */
    public function __construct($obj)
    {
        $this->obj = $obj;
        if (isset($GLOBALS['_ObjCache'][$obj])) {
            $this->data = $GLOBALS['_ObjCacheSkell'][$obj];
        } else {
            if (! isset($GLOBALS['_ObjCacheSkell']) || ! is_array($GLOBALS['_ObjCacheSkell'])) {
                $GLOBALS['_ObjCacheSkell'] = [];
            }

            if (! isset($GLOBALS['_ObjCache']) || ! is_array($GLOBALS['_ObjCache'])) {
                $GLOBALS['_ObjCache'] = [];
            }

            if (file_exists(\LibreNMS\Config::get('install_dir') . '/includes/caches/' . $obj . '.inc.php')) {
                $data = [];
                include \LibreNMS\Config::get('install_dir') . '/includes/caches/' . $obj . '.inc.php';
                $this->data = $data;
                $GLOBALS['_ObjCacheSkell'][$obj] = $this->data;
                if (! (isset($GLOBALS['_ObjCache'][$obj]) && is_array($GLOBALS['_ObjCache'][$obj]))) {
                    $GLOBALS['_ObjCache'][$obj] = $this->data;
                }
            }
        }//end if
    }

    //end __construct()

    /**
     * Check if data exists
     * @param string $obj Name of Data-Object
     * @return bool
     */
    public function offsetExists($obj)
    {
        if (isset($this->data[$obj])) {
            return true;
        }

        return false;
    }

    //end offsetExists()

    /**
     * Get Data-Object
     * @param string $obj Name of Data-Object
     * @return mixed
     */
    public function offsetGet($obj)
    {
        if (isset($this->data[$obj])) {
            if (isset($this->data[$obj]['value'])) {
                return $this->data[$obj]['value'];
            } elseif (isset($GLOBALS['_ObjCache'][$this->obj][$obj]['value'])) {
                return $GLOBALS['_ObjCache'][$this->obj][$obj]['value'];
            } else {
                $GLOBALS['_ObjCache'][$this->obj][$obj]['value'] = dbFetchRows($this->data[$obj]['query'], isset($this->data[$obj]['params']) ? $this->data[$obj]['params'] : []);
                if (sizeof($GLOBALS['_ObjCache'][$this->obj][$obj]['value']) == 1 && sizeof($GLOBALS['_ObjCache'][$this->obj][$obj]['value'][0]) == 1) {
                    $GLOBALS['_ObjCache'][$this->obj][$obj]['value'] = current($GLOBALS['_ObjCache'][$this->obj][$obj]['value'][0]);
                }

                return $GLOBALS['_ObjCache'][$this->obj][$obj]['value'];
            }
        }
    }

    //end offsetGet()

    /**
     * Overrides internal Cache-Object
     * @param string $obj   Name of Data-Object
     * @param mixed  $value Value
     * @return bool
     */
    public function offsetSet($obj, $value)
    {
        if (! isset($this->data[$obj])) {
            $this->data[$obj] = [];
        }

        $this->data[$obj]['value'] = $value;

        return $this->data[$obj]['value'];
    }

    //end offsetSet()

    /**
     * Reset Data-Object
     * @param string $obj Name of Data-Object
     * @return mixed
     */
    public function offsetUnset($obj)
    {
        unset($this->data[$obj]['value']);

        return true;
    }

    //end offsetUnset()
}//end class
