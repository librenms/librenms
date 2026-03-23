<?php

/**
 * Zabbix-aligned operation lists: Operations (problem), Recovery operations, Update operations.
 *
 * @see https://www.zabbix.com/documentation/current/en/manual/config/notifications/action/operation
 */

namespace LibreNMS\Enum;

abstract class AlertRuleOperationPhase
{
    /** Problem escalations — “Operations” in Zabbix */
    public const PROBLEM = 'problem';

    /** “Recovery operations” in Zabbix */
    public const RECOVERY = 'recovery';

    /** “Update operations” in Zabbix (acknowledgements, etc.) */
    public const UPDATE = 'update';
}
