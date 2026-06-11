<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use LibreNMS\Util\Version;

class SystemController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $version = Version::get();

        return response()->json([
            'data' => [
                'local_ver' => $version->name(),
                'local_sha' => $version->git->commitHash(),
                'local_date' => $version->date(),
                'local_branch' => $version->git->branch(),
                'db_schema' => $version->database(),
                'php_ver' => phpversion(),
                'python_ver' => $version->python(),
                'database_ver' => $version->databaseServer(),
                'rrdtool_ver' => $version->rrdtool(),
                'netsnmp_ver' => $version->netSnmp(),
            ],
        ]);
    }
}
