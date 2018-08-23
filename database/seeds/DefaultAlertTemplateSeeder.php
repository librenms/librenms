<?php

use Illuminate\Database\Seeder;

class DefaultAlertTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('alert_templates')->insert([
            [
                'name' => 'BGP Sessions.',
                'template' => '%title\\r\\n\nSeverity: %severity\\r\\n\n{if %state == 0}Time elapsed: %elapsed\\r\\n{/if}\nTimestamp: %timestamp\\r\\n\nUnique-ID: %uid\\r\\n\nRule: {if %name}%name{else}%rule{/if}\\r\\n\n{if %faults}Faults:\\r\\n\n{foreach %faults}\n#%key: %value.string\\r\\n\nPeer: %value.astext\\r\\n\nPeer IP: %value.bgpPeerIdentifier\\r\\n\nPeer AS: %value.bgpPeerRemoteAs\\r\\n\nPeer EstTime: %value.bgpPeerFsmEstablishedTime\\r\\n\nPeer State: %value.bgpPeerState\\r\\n\n{/foreach}\n{/if}',
                'title' => '',
                'title_rec' => ''
            ],
            [
                'name' => 'Ports',
                'template' => '%title\\r\\n\nSeverity: %severity\\r\\n\n{if %state == 0}Time elapsed: %elapsed{/if}\nTimestamp: %timestamp\nUnique-ID: %uid\nRule: {if %name}%name{else}%rule{/if}\\r\\n\n{if %faults}Faults:\\r\\n\n{foreach %faults}\\r\\n\n#%key: %value.string\\r\\n\nPort: %value.ifName\\r\\n\nPort Name: %value.ifAlias\\r\\n\nPort Status: %value.message\\r\\n\n{/foreach}\\r\\n\n{/if}\n',
                'title' => '',
                'title_rec' => ''
            ],
            [
                'name' => 'Temperature',
                'template' => '%title\\r\\n\nSeverity: %severity\\r\\n\n{if %state == 0}Time elapsed: %elapsed{/if}\\r\\n\nTimestamp: %timestamp\\r\\n\nUnique-ID: %uid\\r\\n\nRule: {if %name}%name{else}%rule{/if}\\r\\n\n{if %faults}Faults:\\r\\n\n{foreach %faults}\\r\\n\n#%key: %value.string\\r\\n\nTemperature: %value.sensor_current\\r\\n\nPrevious Measurement: %value.sensor_prev\\r\\n\n{/foreach}\\r\\n\n{/if}',
                'title' => '',
                'title_rec' => ''
            ],
            [
                'name' => 'Default Alert Template',
                'template' => '%title\r\nSeverity: %severity\r\n{if %state == 0}Time elapsed: %elapsed\r\n{/if}Timestamp: %timestamp\r\nUnique-ID: %uid\r\nRule: {if %name}%name{else}%rule{/if}\r\n{if %faults}Faults:\r\n{foreach %faults}  #%key: %value.string\r\n{/foreach}{/if}Alert sent to: {foreach %contacts}%value <%key> {/foreach}',
                'title' => null,
                'title_rec' => null
            ]
        ]);
    }
}
