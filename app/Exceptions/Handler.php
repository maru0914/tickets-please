<?php

namespace App\Exceptions;

use App\Traits\ApiResponses;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponses;

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

    protected array $handlers = [
        ValidationException::class => 'handleValidation',
        ModelNotFoundException::class => 'handleModelNotFound',
        AuthenticationException::class => 'handleAuthentication',
    ];

    private function handleValidation(ValidationException $e): array
    {
        $errors = [];

        foreach ($e->errors() as $key => $value) {
            foreach ($value as $message) {
                $errors[] = [
                    'status' => 422,
                    'message' => $message,
                    'source' => $key,
                ];
            }
        }

        return $errors;
    }

    private function handleModelNotFound(ModelNotFoundException $e): array
    {
        return [
            [
                'status' => 404,
                'message' => 'The resource cannot be found.',
                'source' => $e->getModel(),
            ],
        ];
    }

    private function handleAuthentication(AuthenticationException $e): array
    {
        return [
            [
                'status' => 401,
                'message' => 'Unauthenticated.',
                'source' => '',
            ],
        ];
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): JsonResponse
    {
        $className = get_class($e);

        if (array_key_exists($className, $this->handlers)) {
            $method = $this->handlers[$className];

            return $this->error($this->$method($e));
        }

        $index = strrpos($className, '\\');

        return $this->error([
            [
                'type' => substr($className, $index + 1),
                'status' => 0,
                'message' => $e->getMessage(),
                'source' => 'Line: '.$e->getLine().': '.$e->getFile(),
            ],
        ]);
    }
}
