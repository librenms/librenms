<?php
/**
 * AlertNotes.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Neil Lathwood
 * @copyright  2018 Tony Murray
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace App\Http\Forms;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlertNotes extends BaseForm
{
    protected $validation_messages = [
        'alert_id' => 'Invalid alert id',
        'sub_type' => 'Invalid sub type',
    ];

    public function validationRules()
    {
        return [
            'alert_id' => 'required|integer',
            'sub_type' => Rule::in(['get_note', 'set_note']),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function handleRequest(Request $request)
    {
        $alert = Alert::findOrFail($request->get('alert_id'));

        if ($request->get('sub_type') === 'get_note') {
            return $this->formatResponse(true, 'Alert note retrieved', $alert->pluck('note'));
        } else {
            $alert->note = $request->get('note', '');
            if ($alert->save()) {
                return $this->formatResponse(true, 'Note updated');
            } else {
                return $this->formatResponse(false, 'Could not update note');
            }
        }
    }
}
