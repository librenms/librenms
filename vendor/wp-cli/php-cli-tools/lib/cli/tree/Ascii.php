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
 * The ASCII renderer renders trees with ASCII lines.
 */
class Ascii extends Renderer {

    /**
     * @param array $tree
     * @return string
     */
    public function render(array $tree)
    {
        $output = '';

        $treeIterator = new \RecursiveTreeIterator(
            new \RecursiveArrayIterator($tree),
            \RecursiveTreeIterator::SELF_FIRST
        );

        foreach ($treeIterator as $val)
        {
            $output .= $val . "\n";
        }

        return $output;
    }

}
