<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponser;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [//
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     *
     * @throws Exception|Throwable
     */
    public function report(Throwable $e): void
    {
        if ($e instanceof OAuthServerException || $e instanceof AuthenticationException) {
            if (false) {
                response()->json('Unauthorized', 401);
            } else {
                if ($e instanceof OAuthServerException) {
                    response()->json('Unauthorized', 401);
                }
            }
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception): Response
    {
        if ($exception instanceof ThrottleRequestsException) {
            if ($exception->getHeaders()['Retry-After'] > 60) {
                return $this->errorResponse(
                    __(
                        'auth.throttle_minute',
                        ['minutes' => round($exception->getHeaders()['Retry-After'] / 60)]
                    ),
                    null,
                    $exception->getStatusCode()
                );
            } else {
                return $this->errorResponse(
                    __('auth.throttle', ['seconds' => $exception->getHeaders()['Retry-After']]),
                    null,
                    $exception->getStatusCode()
                );
            }
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse(
                'The specified method for the request is invalid',
                null,
                $exception->getStatusCode()
            );
        }
        if ($exception instanceof NotFoundHttpException) {
            return $this->errorResponse('The specified URL cannot be found', null, $exception->getStatusCode());
        }
        if ($exception instanceof UnauthorizedException) {
            return $this->errorResponse(
                'You do not have the required authorization.',
                null,
                $exception->getStatusCode()
            );
        }
        if ($exception instanceof HttpException) {
            return $this->errorResponse($exception->getMessage(), null, $exception->getStatusCode());
        }
        if ($exception instanceof ModelNotFoundException) {
            return $this->errorResponse('Record not found.', null, 404);
        }

        if ($exception instanceof ValidationException) {
            $firstKey = array_key_first($exception->errors());
            $message = $exception->errors()[$firstKey][0];

            return $this->errorResponse($message, null, 422);
        }

        return parent::render($request, $exception);
    }
}
