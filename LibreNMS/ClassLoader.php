<?php
/**
 * ClassLoader.php
 *
 * PSR-0 and custom class loader for LibreNMS
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;


/**
 * Class ClassLoader
 * @package LibreNMS
 */
class ClassLoader
{
    /**
     * @var array stores dynamically added class > file mappings
     */
    private $classMap;

    /**
     * ClassLoader constructor.
     */
    public function __construct()
    {
        $this->classMap = array();
    }

    /**
     * Loads classes conforming to the PSR-0 specificaton
     *
     * @param string $name Class name to load
     */
    public static function psrLoad($name)
    {
        global $config, $vdebug;
        $file = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $name) . '.php';
        $fullFile = $config['install_dir'] ? $config['install_dir'] . '/' . $file : $file;

        if($vdebug) {
            echo __CLASS__ . " [[ $name > $fullFile ]]\n";
        }

        if (is_readable($fullFile)) {
            include $fullFile;
        }
    }

    /**
     * Loads classes defined by mapClass()
     *
     * @param string $name Class name to load
     */
    public function customLoad($name)
    {
        global $vdebug;
        if (array_key_exists($name, $this->classMap)) {
            $file = $this->classMap[$name];

            if($vdebug) {
                echo __CLASS__ . " (( $name > $file ))\n";
            }

            if (is_readable($file)) {
                include $file;
            }
        }
    }

    /**
     * Add or set a custom class > file mapping
     *
     * @param string $class The full class name
     * @param string $file The path to the file containing the class, full path is preferred
     */
    public function mapClass($class, $file)
    {
        $this->classMap[$class] = $file;
    }

    /**
     * Remove a class from the list of class > file mappings
     *
     * @param string $class The full class name
     */
    public function unMapClass($class)
    {
        unset($this->classMap[$class]);
    }

    /**
     * Register this autoloader
     * Custom mappings will take precedence over PSR-0
     */
    public function register()
    {
        spl_autoload_register(array($this, 'customLoad'));
        spl_autoload_register(__NAMESPACE__.'\ClassLoader::psrLoad');
    }

    /**
     * Unregister this autoloader
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'customLoad'));
        spl_autoload_unregister(__NAMESPACE__.'\ClassLoader::psrLoad');
    }
}
