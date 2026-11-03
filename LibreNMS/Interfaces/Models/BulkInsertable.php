<?php

namespace LibreNMS\Interfaces\Models;

/**
 * Opt in to raw insertion by SyncsModels::syncModelsInBulk().
 * The model must not override persistence or need creation side effects beyond
 * timestamps and ModuleModelObserver progress. Additional event listeners cause
 * a fallback to Eloquent. Callers needing saved instances must use syncModels().
 */
interface BulkInsertable extends Keyable
{
}
