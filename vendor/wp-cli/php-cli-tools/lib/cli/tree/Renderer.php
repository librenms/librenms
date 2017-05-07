<?php
/**
 * PHP Command Line Tools
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @author    Ryan Sullivan <rsullivan@connectstudios.com>
 * @copyright 2010 James Logsdom (http://girsbrain.org)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace cli\tree;

/**
 * Tree renderers are used to change how a tree is displayed.
 */
abstract class Renderer {

    /**
     * @param array $tree
     * @return string|null
     */
    abstract public function render(array $tree);

}
