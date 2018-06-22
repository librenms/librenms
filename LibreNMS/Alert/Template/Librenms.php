<?php
/**
 * Librenms.php
 *
 * Librenms Template class
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

namespace LibreNMS\Alert\Template;

use App\Models\AlertTemplate;

class Librenms extends Template
{
    /**
     *
     * Get the parsed body
     *
     * @param $data
     * @return mixed|string
     */
    public function getBody($data)
    {
        $tpl    = $this->getTemplate($data)->template;
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
     * Get the parsed title
     *
     * @param $data
     * @return mixed|string
     */
    public function getTitle($data)
    {
        if (strstr($this->getTemplate($data)->title, '%')) {
            return RunJail('$ret = "'.populate(addslashes($this->getTemplate($data)->title)).'";', $data);
        } else {
            return $this->getTemplate($data)->title ?: $data['title'];
        }
    }

    /**
     *
     * Get the default template for this parsing engine
     *
     * @return string
     */
    public function getDefaultTemplate()
    {
        return '%title' . PHP_EOL .
            'Severity: %severity' . PHP_EOL .
            '{if %state == 0}Time elapsed: %elapsed{/if}' . PHP_EOL .
            'Timestamp: %timestamp' . PHP_EOL .
            'Unique-ID: %uid' . PHP_EOL .
            'Rule: {if %name}%name{else}%rule{/if}' . PHP_EOL .
            '{if %faults}Faults:' . PHP_EOL .
            '{foreach %faults}  #%key: %value.string{/foreach}{/if}' . PHP_EOL .
            'Alert sent to: {foreach %contacts}%value <%key> {/foreach}';
    }
}
