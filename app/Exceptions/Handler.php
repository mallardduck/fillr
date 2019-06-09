<?php

namespace App\Exceptions;

use Exception;
use App\Exceptions\SizeException;
use App\Services\FillService\ServerException;
use App\Services\FillService\UnsupportedType;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Contracts\View\Factory as View;
use Illuminate\Http\Response;

class Handler extends ExceptionHandler
{

    /**
     * @var View
     */
    private $view;

    /**
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof SizeException) {
            return (new Response(
                $this->view->make(
                    'error',
                    [
                      'title'     => 'Bad Request',
                      'subdomain' => $request->subdomain,
                      'message'   => $exception->getMessage(),
                    ]
                ),
                400
            ));
        } elseif ($exception instanceof ServerException) {
            return (new Response(
                $this->view->make(
                    'error',
                    [
                      'title'     => 'Server Error',
                      'subdomain' => $request->subdomain,
                      'message'   => "There was an error on the server. Potentially you can try again in a few minutes and it may work. If it doesn't then it's likely it won't start working.",
                    ]
                ),
                500
            ));
        } elseif ($exception instanceof UnsupportedType) {
            $base_size = preg_replace('/^[A-Za-z]+\//', '', $request->path());
            return (new Response(
                $this->view->make(
                    'error',
                    [
                      'title'     => 'Bad Request',
                      'subdomain' => $request->subdomain,
                      'message'   => $exception->getMessage() . ' Redirecting...',
                      'refresh'   => url($base_size),
                    ]
                ),
                400
            ));
        }

        return parent::render($request, $exception);
    }
}
