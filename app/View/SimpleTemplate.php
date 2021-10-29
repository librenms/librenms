<?php
/*
 * SimpleTemplate.php
 *
 * Simple variable substitution template
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

namespace App\View;

use LibreNMS\Util\StringHelpers;

class SimpleTemplate
{
    /**
     * @var string
     */
    private $template;
    /**
     * @var array
     */
    private $variables;
    /**
     * @var string
     */
    private $regex = '/{{ \$?([a-zA-Z0-9\-_.:]+) }}/';
    /**
     * @var callable
     */
    private $callback;

    public function __construct(string $template, array $variables = [])
    {
        $this->template = $template;
        $this->variables = $variables;
    }

    public function setVariable(string $key, string $value): void
    {
        $this->variables[$key] = $value;
    }

    public function replaceWith(callable $callback): void
    {
        $this->callback = $callback;
    }

    /**
     * Create and parse a simple template
     *
     * @param  string  $template
     * @param  array  $variables
     * @return string
     */
    public static function parse(string $template, array $variables): string
    {
        return (string) new static($template, $variables);
    }

    public function __toString()
    {
        return preg_replace_callback($this->regex, $this->callback ?? function ($matches) {
            $replacement = $this->variables[$matches[1]] ?? '';
            if (! StringHelpers::isStringable($replacement)) {
                return '';
            }

            return $replacement;
        }, $this->template);
    }

}
