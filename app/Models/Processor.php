<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Processor extends DeviceRelatedModel
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'processor_id';

    // ---- Helper Functions ----

    /**
     * Return Processor Description, formatted for display
     *
     * @return string
     */
    public function getFormattedDescription()
    {
        $bad_descr = [
            'GenuineIntel:',
            'AuthenticAMD:',
            'Intel(R)',
            'CPU',
            '(R)',
            '(tm)',
        ];

        $descr = str_replace($bad_descr, '', $this->processor_descr);

        // reduce extra spaces
        $descr = str_replace('  ', ' ', $descr);

        return $descr;
    }
}
