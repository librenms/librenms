<?php

// Plugins
if (!empty($agent_data['munin'])) {
    echo 'Munin Plugins:';
    d_echo($agent_data['munin']);

    // Build array of existing plugins
    $plugins_dbq = dbFetchRows('SELECT * FROM `munin_plugins` WHERE `device_id` = ?', array($device['device_id']));
    foreach ($plugins_dbq as $plugin_db) {
        $plugins_db[$plugin_db['mplug_type']]['id'] = $plugin_db['mplug_id'];
    }

    $old_plugins_rrd_dir = $host_rrd.'/plugins';
    $plugins_rrd_dir     = $host_rrd.'/munin';
    if (is_dir($old_plugins_rrd_dir) && !is_dir($plugins_rrd_dir)) {
        rename($old_plugins_dir, $plugins_dir);
    }

    if (!is_dir($plugins_rrd_dir)) {
        mkdir($plugins_rrd_dir);
        echo "Created directory : $plugins_rrd_dir\n";
    }

    $plugin = array();
    foreach ($agent_data['munin'] as $plugin_type => $plugin_data) {
        $plugin = array();
        // list($plugin_type, $instance) = explode("_", $plugin_type);
        // if (!empty($instance))
        // {
        // echo("\nPlugin: $plugin_type ($instance)");
        // $plugin_rrd = $plugins_rrd_dir . "/" . $plugin_type."_".$instance;
        // $plugin_uniq = $plugin_type."_".$instance;
        // } else {
        echo "\nPlugin: $plugin_type";
        $plugin_rrd  = $plugins_rrd_dir.'/'.$plugin_type;
        $plugin_uniq = $plugin_type.'_';
        // }
        d_echo("\n[$plugin_data]\n");

        foreach (explode("\n", $plugin_data) as $line) {
            list($key, $value) = explode(' ', $line, 2);
            if (preg_match('/^graph_/', $key)) {
                list(,$key)            = explode('_', $key);
                $plugin['graph'][$key] = $value;
            }
            else {
                list($metric,$key)               = explode('.', $key);
                $plugin['values'][$metric][$key] = $value;
            }
        }

        if (!is_array($plugins_db[$plugin_type])) {
            $insert   = array(
                'device_id'      => $device['device_id'],
                'mplug_type'     => $plugin_type,
                'mplug_instance' => ($instance == null ? array('NULL') : $instance),
                'mplug_category' => ($plugin['graph']['category'] == null ? 'general' : strtolower($plugin['graph']['category'])),
                'mplug_title'    => ($plugin['graph']['title'] == null ? array('NULL') : $plugin['graph']['title']),
                'mplug_vlabel'   => ($plugin['graph']['vlabel'] == null ? array('NULL') : $plugin['graph']['vlabel']),
                'mplug_args'     => ($plugin['graph']['args'] == null ? array('NULL') : $plugin['graph']['args']),
                'mplug_info'     => ($plugin['graph']['info'] == null ? array('NULL') : $plugin['graph']['info']),
            );
            $mplug_id = dbInsert($insert, 'munin_plugins');
        }
        else {
            $mplug_id = $plugins_db[$plugin_type]['id'];
        }

        if ($mplug_id) {
            echo " ID: $mplug_id";

            $dbq = dbFetchRows('SELECT * FROM `munin_plugins_ds` WHERE `mplug_id` = ?', array($mplug_id));
            foreach ($dbq as $v) {
                $vu           = $v['mplug_id'].'_'.$v['ds_name'];
                $ds_list[$vu] = 1;
            }

            unset($dbq);
            unset($v);

            foreach ($plugin['values'] as $name => $data) {
                echo " $name";
                if (empty($data['type'])) {
                    $data['type'] = 'GAUGE';
                }

                if (empty($data['graph'])) {
                    $data['graph'] = 'yes';
                }

                if (empty($data['label'])) {
                    $data['label'] = $name;
                }

                if (empty($data['draw'])) {
                    $data['draw'] = 'LINE1.5';
                }

                $cmd      = '--step 300 DS:val:'.$data['type'].':600:U:U ';
                $cmd     .= $config['rrd_rra'];
                $ds_uniq  = $mplug_id.'_'.$name;
                $filename = $plugin_rrd.'_'.$name.'.rrd';
                if (!is_file($filename)) {
                    rrdtool_create($filename, $cmd);
                }

                $fields = array(
                    'val' => $data['value'],
                );

                rrdtool_update($filename, $fields);

                if (empty($ds_list[$ds_uniq])) {
                    $insert = array(
                        'mplug_id'    => $mplug_id,
                        'ds_name'     => $name,
                        'ds_type'     => $data['type'],
                        'ds_label'    => $data['label'],
                        'ds_cdef'     => $data['cdef'],
                        'ds_draw'     => $data['draw'],
                        'ds_info'     => $data['info'],
                        'ds_extinfo'  => $data['extinfo'],
                        'ds_min'      => $data['min'],
                        'ds_max'      => $data['max'],
                        'ds_graph'    => $data['graph'],
                        'ds_negative' => $data['negative'],
                        'ds_warning'  => $data['warning'],
                        'ds_critical' => $data['critical'],
                        'ds_colour'   => $data['colour'],
                        'ds_sum'      => $data['sum'],
                        'ds_stack'    => $data['stack'],
                        'ds_line'     => $data['line'],
                    );
                    $ds_id  = dbInsert($insert, 'munin_plugins_ds');
                }//end if
            }//end foreach
        }
        else {
            echo "No ID!\n";
        }//end if
    }//end foreach
}//end if
