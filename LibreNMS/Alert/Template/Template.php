<?php
/**
 * Created by PhpStorm.
 * User: neillathwood
 * Date: 02/06/2018
 * Time: 21:38
 */

namespace LibreNMS\Alert\Template;

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
        $this->$template = AlertTemplate::whereHas('map', function ($query) use ($obj) {
            $query->where('alert_rule_id', '=', $obj['rule_id']);
        })->first();
        if (!$this->$template) {
            $this->$template = AlertTemplate::where('name', '=', 'Default Alert Template')->first();
        }
        return $this->$template;
    }
}
