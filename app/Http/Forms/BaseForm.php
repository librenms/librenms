<?php
/**
 * BaseForm.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Forms;

use Illuminate\Http\Request;

abstract class BaseForm
{
    protected $validation_rules = [];
    protected $validation_messages = [];

    /**
     * Return custom validation messages for this request
     * https://laravel.com/docs/validation
     *
     * @return array
     */
    public function validationMessages()
    {
        return $this->validation_messages;
    }

    /**
     * Return validation rules for this request
     * https://laravel.com/docs/validation
     *
     * @return array
     */
    public function validationRules()
    {
        return $this->validation_rules;
    }

    /**
     * @param Request $request
     * @return array
     */
    abstract public function handleRequest(Request $request);

    /**
     * @param bool $status
     * @param string $message
     * @param array $additional
     * @return array
     */
    protected function formatResponse($status, $message, $additional = [])
    {
        $base = [
            'status'       => $status ? 'ok' : 'error',
            'message'      => $message,
        ];

        return array_merge($base, $additional);
    }
}
