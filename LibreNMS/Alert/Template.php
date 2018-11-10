<?php
/**
 * Template.php
 *
 * Base Template class
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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace LibreNMS\Alert;

use App\Models\AlertTemplate;

class Template
{
    public $template;

    /**
     *
     * Get the template details
     *
     * @param null $obj
     * @return mixed
     */
    public function getTemplate($obj = null)
    {
        if ($this->template) {
            // Return the cached template information.
            return $this->template;
        }
        $this->template = AlertTemplate::whereHas('map', function ($query) use ($obj) {
            $query->where('alert_rule_id', '=', $obj['rule_id']);
        })->first();
        if (!$this->template) {
            $this->template = AlertTemplate::where('name', '=', 'Default Alert Template')->first();
        }
        return $this->template;
    }

    public function getTitle($data)
    {
        $data['parsed_title'] = $this->bladeTitle($data);
        //FIXME remove Deprecated template
        return $this->legacyTitle($data);
    }

    public function getBody($data)
    {
        $data['template']['parsed_template'] = $this->bladeBody($data);
        //FIXME remove Deprecated template
        return $this->legacyBody($data);
    }

    /**
     *
     * Parse Blade body
     *
     * @param $data
     * @return string
     */
    public function bladeBody($data)
    {
        $alert['alert'] = new AlertData($data['alert']);
        try {
            return view(['template' => $data['template']->template], $alert)->__toString();
        } catch (\Exception $e) {
            return view(['template' => $this->getDefaultTemplate()], $alert)->__toString();
        }
    }

    /**
     *
     * Parse Blade title
     *
     * @param $data
     * @return string
     */
    public function bladeTitle($data)
    {
        $alert['alert'] = new AlertData($data['alert']);
        try {
            return view(['template' => $data['title']], $alert)->__toString();
        } catch (\Exception $e) {
            return $data['title'] ?: view(['template' => "Template " . $data['name']], $alert)->__toString();
        }
    }

    /**
     *
     * Parse legacy body
     *
     * @param $data
     * @return mixed|string
     */
    public function legacyBody($data)
    {
        //FIXME remove Deprecated template
        $tpl    = $data['template']->parsed_template;
        $msg    = '$ret .= "'.str_replace(array('{else}', '{/if}', '{/foreach}'), array('"; } else { $ret .= "', '"; } $ret .= "', '"; } $ret .= "'), addslashes($tpl)).'";';
        $parsed = $msg;
        $s      = strlen($msg);
        $x      = $pos = -1;
        $buff   = '';
        $if     = $for = $calc = false;
        while (++$x < $s) {
            if ($msg[$x] == '{' && $buff == '') {
                $buff .= $msg[$x];
            } elseif ($buff == '{ ') {
                $buff = '';
            } elseif ($buff != '') {
                $buff .= $msg[$x];
            }

            if ($buff == '{if') {
                $pos = $x;
                $if  = true;
            } elseif ($buff == '{foreach') {
                $pos = $x;
                $for = true;
            } elseif ($buff == '{calc') {
                $pos  = $x;
                $calc = true;
            }

            if ($pos != -1 && $msg[$x] == '}') {
                $orig = $buff;
                $buff = '';
                $pos  = -1;
                if ($if) {
                    $if     = false;
                    $o      = 3;
                    $native = array(
                        '"; if( ',
                        ' ) { $ret .= "',
                    );
                } elseif ($for) {
                    $for    = false;
                    $o      = 8;
                    $native = array(
                        '"; foreach( ',
                        ' as $key=>$value) { $ret .= "',
                    );
                } elseif ($calc) {
                    $calc   = false;
                    $o      = 5;
                    $native = array(
                        '"; $ret .= (float) (0+(',
                        ')); $ret .= "',
                    );
                } else {
                    continue;
                }

                $cond   = trim(populate(substr($orig, $o, -1), false));
                $native = $native[0].$cond.$native[1];
                $parsed = str_replace($orig, $native, $parsed);
                unset($cond, $o, $orig, $native);
            }//end if
        }//end while
        $parsed = populate($parsed);
        return RunJail($parsed, $data);
    }

    /**
     *
     * Parse legacy title
     *
     * @param $data
     * @return mixed|string
     */
    public function legacyTitle($data)
    {
        //FIXME remove Deprecated template
        if (strstr($data['parsed_title'], '%')) {
            return RunJail('$ret = "'.populate(addslashes($data['parsed_title'])).'";', $data);
        } else {
            return $data['parsed_title'];
        }
    }

    /**
     *
     * Get the default template
     *
     * @return string
     */
    public function getDefaultTemplate()
    {
        return '{{ $alert->title }}' . PHP_EOL .
            'Severity: {{ $alert->severity }}' . PHP_EOL .
            '@if ($alert->state == 0)Time elapsed: {{ $alert->elapsed }} @endif ' . PHP_EOL .
            'Timestamp: {{ $alert->timestamp }}' . PHP_EOL .
            'Unique-ID: {{ $alert->uid }}' . PHP_EOL .
            'Rule: @if ($alert->name) {{ $alert->name }} @else {{ $alert->rule }} @endif ' . PHP_EOL .
            '@if ($alert->faults)Faults:' . PHP_EOL .
            '@foreach ($alert->faults as $key => $value)' . PHP_EOL .
            '  #{{ $key }}: {{ $value[\'string\'] }} @endforeach' . PHP_EOL .
            '@endif' . PHP_EOL .
            'Alert sent to: @foreach ($alert->contacts as $key => $value) {{ $value }} <{{ $key }}> @endforeach';
    }
}
