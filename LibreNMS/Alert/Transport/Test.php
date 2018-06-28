<?php
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Mail Transport
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */
namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;

class Test extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        return true;
    }

    public static function configTemplate()
    {
        return [
            [
                'title' => 'Textbox',
                'name' => 'textbox',
                'descr' => 'Email address of contact',
                'type'  => 'text',
                'required' => true,
                'pattern' => '[a-zA-Z0-9_\-\.\+]+@\[a-zA-Z0-9_\-\.]+\.[a-zA-Z]{2,18}'
            ],
            [
                'title' => 'Checkbox',
                'name' => 'checkbox',
                'descr' => 'Something to check',
                'type' => 'checkbox',
                'default' => true,
            ],
            [
                'title' => 'Anothercheck',
                'name' => 'test-check',
                'descr' => 'Another test',
                'type' => 'checkbox',
                'default' => false
            ],
            [
                'title' => 'Select',
                'name' => 'select',
                'descr' => 'something to select',
                'type' => 'select',
                'options' => [
                    'Option A' => 'value',
                    'Option B' => 'anothervalue',
                    'Option C' => 'test'
                ]
            ]
        ];
    }

    public static function configBuilder($vars)
    {
        $status = 'ok';
        $message = '';

        if ($vars['textbox']) {
            $transport_config = array(
                'textbox' => $vars['textbox'],
                'select' => $vars['select']
            );

            if (empty($vars['checkbox'])) {
                $transport_config['checkbox'] = false;
            } else {
                $transport_config['checkbox'] = true;
            }

            if (empty($vars['test-check'])) {
                $transport_config['test-check'] = false;
            } else {
                $transport_config['test-check'] = true;
            }
        } else {
            $status = 'error';
            $message = 'Missing information';
        }

        return [
            'transport_config' => $transport_config,
            'status' => $status,
            'message' => $message
        ];
    }
}
