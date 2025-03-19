<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\Access\AuthorizationException; // Importante
use Symfony\Component\HttpFoundation\Response; // Importante

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'No tienes permiso para realizar esta acción.'], Response::HTTP_FORBIDDEN);
            }

            return redirect()->back()->with('error', 'No tienes permiso para realizar esta acción.');
        });
    }
}
