<?php

/**
 * Operation lists: Operations (problem), Recovery operations, Update operations.
 */

namespace LibreNMS\Enum;

abstract class AlertRuleOperationPhase
{
    /** Problem escalations” */
    public const PROBLEM = 'problem';

    /** “Recovery operations” */
    public const RECOVERY = 'recovery';

    /** “Update operations”  (acknowledgements, etc.) */
    public const UPDATE = 'update';
}
