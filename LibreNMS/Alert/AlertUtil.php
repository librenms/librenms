<?php
namespace LibreNMS\Alert;

class AlertUtil
{
    // Return rule id from alert id
    private static function getRuleId($alert_id)
    {
        $query = "SELECT `rule_id` FROM `alerts` WHERE `id`=?";
        return dbFetchCell($query, [$alert_id]);
    }

    // Return all alert transports mapped to a rule (includies transport groups)
    // @retyurn array [$transport_id $transport_type]
    public static function getAlertTransports($alert_id)
    {
        // Query for list of transport ids
        $query = "SELECT b.transport_id, b.transport_type FROM alert_transport_map AS a LEFT JOIN alert_transports AS b ON b.transport_id=a.transport_or_group_id WHERE a.target_type='single' AND a.rule_id=? UNION DISTINCT SELECT d.transport_id, d.transport_type FROM alert_transport_map AS a LEFT JOIN alert_transport_groups AS b ON a.transport_or_group_id=b.transport_group_id LEFT JOIN transport_group_transport AS c ON b.transport_group_id=c.transport_group_id LEFT JOIN alert_transports AS d ON c.transport_id=d.transport_id WHERE a.target_type='group' AND a.rule_id=?";
        $rule_id = self::getRuleId($alert_id);
        return dbFetchRows($query, [$rule_id, $rule_id]);
    }

    // Return transports configured as default
    // @return array [$transport_id $transport_type]
    public static function getDefaultAlertTransports()
    {
        $query = "SELECT transport_id, transport_type FROM alert_transports WHERE is_default=true";
        return dbFetchRows($query);
    }

    // Return list of transport types with a default configured
    public static function getDefaultTransportList()
    {
        $query = "SELECT DISTINCT transport_type FROM alert_transports WHERE is_default=true ";
        return dbFetchColumn($query);
    }
}
