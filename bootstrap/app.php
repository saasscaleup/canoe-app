<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Str;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withEvents(discover: [
        __DIR__.'/../app/Listeners',
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {

            if ($request->is('api/*')) {

                // Handle 404 Not Found errors
                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'message' => 'Record not found.'
                    ], 404);
                }
                
                // handle 422 Unprocessable Entity errors
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return null;
                }

                /* 
                / Handle all other errors
                */

                
                // Generate a UUID for tracking
                $uuid = (string) Str::uuid();

                // Log the exception with the UUID
                // We can also log to a database or log to a third party service
                reportError($e, $uuid);

                return response()->json([
                    'message' => 'An internal server error occurred - ' . $uuid
                ], 500);
            }

            // For non-API routes, return null to let the default handler take over
            return null;
        });
    })->create();
