<?php

namespace App\Http\Controllers;

use App\Models\AlertTemplate;
use Illuminate\Http\Response;

class AlertTemplateController extends Controller
{
    /**
     * Remove the specified alert template from storage.
     *
     * @param  AlertTemplate  $alertTemplate
     * @return Response
     */
    public function destroy(AlertTemplate $alertTemplate): Response
    {
        $this->authorize('delete', $alertTemplate);

        if ($alertTemplate->delete()) {
            $alertTemplate->map()->delete();

            return response('Alert template has been deleted.');
        }

        return response('ERROR: Alert template has not been deleted.', 500);
    }
}
