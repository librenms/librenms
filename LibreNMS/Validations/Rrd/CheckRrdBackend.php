<?php

namespace LibreNMS\Validations\Rrd;

use App\Facades\LibrenmsConfig;
use LibreNMS\Interfaces\Validation;
use LibreNMS\ValidationResult;

class CheckRrdBackend implements Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        if (LibrenmsConfig::get('rrd.backend') === 'php-rrd' && ! extension_loaded('rrd')) {
            return ValidationResult::fail(
                trans('validation.validations.rrd.CheckRrdBackend.fail_extension'),
                trans('validation.validations.rrd.CheckRrdBackend.fix_backend'),
            );
        }

        return ValidationResult::ok(trans('validation.validations.rrd.CheckRrdBackend.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return LibrenmsConfig::get('rrd.backend', 'rrdtool') !== 'rrdtool';
    }
}
