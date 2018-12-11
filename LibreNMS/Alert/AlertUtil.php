<?php
namespace LibreNMS\Alert;

class AlertUtil
{
    /**
     *
     * Get the rule_id for a specific alert
     *
     * @param $alert_id
     * @return mixed|null
     */
    private static function getRuleId($alert_id)
    {
        $query = "SELECT `rule_id` FROM `alerts` WHERE `id`=?";
        return dbFetchCell($query, [$alert_id]);
    }

    /**
     *
     * Get the transport for a given alert_id
     *
     * @param $alert_id
     * @return array
     */
    public static function getAlertTransports($alert_id)
    {
        $query = "SELECT b.transport_id, b.transport_type, b.transport_name FROM alert_transport_map AS a LEFT JOIN alert_transports AS b ON b.transport_id=a.transport_or_group_id WHERE a.target_type='single' AND a.rule_id=? UNION DISTINCT SELECT d.transport_id, d.transport_type, d.transport_name FROM alert_transport_map AS a LEFT JOIN alert_transport_groups AS b ON a.transport_or_group_id=b.transport_group_id LEFT JOIN transport_group_transport AS c ON b.transport_group_id=c.transport_group_id LEFT JOIN alert_transports AS d ON c.transport_id=d.transport_id WHERE a.target_type='group' AND a.rule_id=?";
        $rule_id = self::getRuleId($alert_id);
        return dbFetchRows($query, [$rule_id, $rule_id]);
    }

    /**
     *
     * Returns the default transports
     *
     * @return array
     */
    public static function getDefaultAlertTransports()
    {
        $query = "SELECT transport_id, transport_type, transport_name FROM alert_transports WHERE is_default=true";
        return dbFetchRows($query);
    }
}
