<?php

use Illuminate\Support\Facades\Log;

if (!function_exists('reportError')) {
    /**
     * Log the exception with the UUID
     *
     * @param Throwable $e
     * @param string $uuid
     * @return void
     */
    function reportError(Throwable $e, string $uuid): void
    {
        // Log the exception with the UUID
        Log::error("Exception: {$e->getMessage()}", [
            'UUID' => $uuid,
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
