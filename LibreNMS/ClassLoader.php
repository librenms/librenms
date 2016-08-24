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
     * @var array stores dynamically added class > file mappings ($classMap[fullclass] = $file)
     */
    private $classMap;

    /**
     * @var array stores dynamically added namespace > directory mappings ($dirMap[namespace][$dir] =1)
     */
    private $dirMap;

    /**
     * ClassLoader constructor.
     */
    public function __construct()
    {
        $this->classMap = array();
        $this->dirMap = array();
    }

    /**
     * Loads classes conforming to the PSR-0 specificaton
     *
     * @param string $name Class name to load
     */
    public static function psrLoad($name)
    {
        global $vdebug;

        $file = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $name) . '.php';
        $fullFile = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR .  $file;

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

        if (isset($this->classMap[$name])) {
            $file = $this->classMap[$name];

            if($vdebug) {
                echo __CLASS__ . " (( $name > $file ))\n";
            }

            if (is_readable($file)) {
                include $file;
                return;
            }
        }

        list($namespace, $class) = $this->splitNamespace($name);
        if (isset($this->dirMap[$namespace])) {
            foreach (array_keys($this->dirMap[$namespace]) as $dir) {
                $file = $dir . DIRECTORY_SEPARATOR . $class . '.php';

                if($vdebug) {
                    echo __CLASS__ . " (( $name > $file ))\n";
                }

                if (is_readable($file)) {
                    include $file;
                    return;
                }
            }
        }
    }

    /**
     * Register a custom class > file mapping
     *
     * @param string $class The full class name
     * @param string $file The path to the file containing the class, full path is preferred
     */
    public function registerClass($class, $file)
    {
        $this->classMap[$class] = $file;
    }

    /**
     * Unregister a class from the list of class > file mappings
     *
     * @param string $class The full class name
     */
    public function unregisterClass($class)
    {
        unset($this->classMap[$class]);
    }

    /**
     * Register a directory to search for classes in.
     * If a namespace is specified, it will search for
     * classes with that exact namespace in those directories.
     *
     * @param string $dir directory containing classes with filename = class.php
     * @param string $namespace the namespace of the classes
     */
    public function registerDir($dir, $namespace = '')
    {
        $this->dirMap[$namespace][$dir] = 1;
    }

    /**
     * Unregister a directory
     *
     * @param string $dir the directory to remove
     * @param string $namespace the namespace of the classes
     */
    public function unregisterDir($dir, $namespace = '')
    {
        unset($this->dirMap[$namespace][$dir]);
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

    /**
     * Split a class into namspace/classname
     * @param string $class the full class name to split
     * @return array of the split class [namespace, classname]
     */
    private function splitNamespace($class) {
        $parts = explode('\\', $class);
        $last = array_pop($parts);
        return array(implode('\\', $parts), $last);
    }
}
