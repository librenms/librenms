<?php
/**
 * Created by PhpStorm.
 * User: neillathwood
 * Date: 02/06/2018
 * Time: 21:38
 */

namespace LibreNMS\Alert;

use App\Models\AlertTemplate;
use App\Models\AlertTemplateMap;

class Template
{
    public static function format($obj)
    {
        if ($obj['template_type'] === 'librenms') {
            return self::librenms($obj);
        } else {
            return self::blade($obj);
        }
    }

    private static function blade($obj)
    {
        print_r($obj['rule_id']);
        $tpl = AlertTemplateMap::with('template')->where('alert_rule_id', '=', $obj['rule_id'])->first();
        if (!$tpl) {
            $tpl = AlertTemplate::where('name', '=', 'Default Alert Template')->first();
        }
        echo \DbView::make($tpl, ['foo' => 'Bar'], [], 'template')->render();exit;
    }

    /**
     * Format Alert
     * @param array  $obj Alert-Array
     * @return string
     */
    private static function librenms($obj)
    {
        $tpl    = $obj["template"];
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
        return RunJail($parsed, $obj);
    }//end FormatAlertTpl()
}