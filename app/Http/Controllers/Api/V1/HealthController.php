<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\HealthCheckException;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LibreNMS\Util\Version;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            return response()->json(['meta' => [
                'status' => '200',
                'version' => Version::VERSION,
                'checks' => [
                    ...$this->checkDatabase(),
                    ...$this->checkCache(),
                ],
            ]]);
        } catch (HealthCheckException $e) {
            return response()->json(['errors' => [
                'status' => '503',
                'code' => $e->errorCode,
                'title' => $e->title,
                'detail' => $e->detail,
                'meta' => [
                    'component' => $e->component,
                    'timestamp' => now()->toIso8601ZuluString(),
                ]
            ]], 503);
        }
    }

    /**
     * @return array{string, string: 'up'}
     * @throws HealthCheckException
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->select('SELECT 1');

            return ['database' => 'up'];
        } catch (\Exception $e) {
            throw new HealthCheckException('database', 'DATABASE_DISCONNECTED', 'Service Unavailable', $e->getMessage());
        }
    }

    /**
     * @return array{string, string: 'up'}
     * @throws HealthCheckException
     */
    private function checkCache()
    {
        $key = 'health:ping:' . Str::random();

        Cache::put($key, '1', 5);
        $fetched = Cache::get($key);
        Cache::forget($key);

        if ($fetched !== '1') {
            throw new HealthCheckException('cache', 'CACHE_DISCONNECTED', 'Service Unavailable', '');
        }

        return ['cache' => 'up'];
    }
}
