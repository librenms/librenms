<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check(fn () => DB::connection()->select('SELECT 1')),
            'cache' => $this->check(function () {
                $key = 'health:ping:' . bin2hex(random_bytes(4));
                Cache::put($key, '1', 5);
                if (Cache::get($key) !== '1') {
                    throw new \RuntimeException('cache round-trip mismatch');
                }
                Cache::forget($key);
            }),
        ];

        $status = collect($checks)->every(fn ($c) => $c['ok']) ? 'ok' : 'down';
        $httpStatus = $status === 'ok' ? 200 : 503;

        return response()->json([
            'status' => $status,
            'checks' => $checks,
        ], $httpStatus);
    }

    private function check(callable $probe): array
    {
        try {
            $probe();

            return ['ok' => true];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
