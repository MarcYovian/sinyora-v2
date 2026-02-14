<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Health Check Routes
|--------------------------------------------------------------------------
|
| Simple health check endpoint for deployment verification and monitoring.
| This endpoint verifies database, cache, and application status.
|
*/

Route::get('/deployment-health', function () {
    $status = 'healthy';
    $checks = [];

    // Check database connectivity
    try {
        DB::connection()->getPdo();
        $checks['database'] = 'ok';
    } catch (\Exception $e) {
        $checks['database'] = 'error';
        $status = 'unhealthy';
    }

    // Check cache connectivity
    try {
        Cache::put('health_check', true, 10);
        $cacheWorks = Cache::get('health_check') === true;
        $checks['cache'] = $cacheWorks ? 'ok' : 'error';
        
        if (!$cacheWorks) {
            $status = 'unhealthy';
        }
    } catch (\Exception $e) {
        $checks['cache'] = 'error';
        $status = 'unhealthy';
    }

    // Check storage writable
    try {
        $testFile = storage_path('logs/health_check.tmp');
        file_put_contents($testFile, 'test');
        $checks['storage'] = file_exists($testFile) ? 'ok' : 'error';
        @unlink($testFile);
        
        if ($checks['storage'] === 'error') {
            $status = 'unhealthy';
        }
    } catch (\Exception $e) {
        $checks['storage'] = 'error';
        $status = 'unhealthy';
    }

    return response()->json([
        'status' => $status,
        'timestamp' => now()->toIso8601String(),
        'checks' => $checks,
        'app' => config('app.name'),
        'environment' => app()->environment(),
    ], $status === 'healthy' ? 200 : 503);
});
