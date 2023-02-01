<?php

namespace Acelle\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Closure;
use Acelle\Library\Notification\BackendError as BackendErrorNotification;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
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
        if (in_array(get_class($exception), $this->dontReport)) {
            // skip logging as those exceptions are handled by Laravel Auth
        } else if (isInitiated()) {
            try {
                // IMPORTANT: do not use Model here, what if DB connect is not initiated correctly, exception loop stack => "PHP Fatal error:  Uncaught Error: Maximum function nesting level of '256' reached, aborting"
                // check if the exception does match the known list
                // $errorTitle = $exception->getMessage();
                // BackendErrorNotification::cleanupDuplicateNotifications($errorTitle);
                // BackendErrorNotification::warning([ 'title' => $errorTitle, 'message' => date('Y-m-d').' ('.get_class($exception).') '.$exception->getMessage() ], false);
            } catch (Exception $e) {
                // just keep silent
            }
        }

        parent::report($exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return mixed
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->wantsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $e
     *
     * @return mixed
     */
    public function render($request, Exception $e)
    {

        if ($request->wantsJson() && $e instanceof ValidationException) {
            return response()->json([
                'message' => '', // $exception->getMessage(),
                'validation_errors' => $this->getFormikValidationErrors($e)
            ], 400);
        }
        // With 404 error, no way to use response()->view
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return \Response::make(sprintf('<h1>404 - Not found</h1><p>Go back to your home page at <a href="%1$s">%1$s</a></p>', url('/')), 404);
        }

        // check if the exception does match the known list
        foreach ($this->customErrors() as $key => $value) {
            if ($key == get_class($e)) {
                // if the exception message does match
                if (array_key_exists('match', $value)) {
                    if (strpos($e->getMessage(), $value['match']) !== false) {
                        // error description does match, ok
                    } else {
                        continue;
                    }
                }
                
                // If message is a string, that's fine
                // If message is a Closure, execute it to get the actual message
                // Normally, we pass the exception instance to the closure to generate more detailed/specific message
                if ($value['message'] instanceof Closure) {
                    $value['message'] = $value['message']($e);
                }
                
                return response()->view('errors.custom', $value, 500);
            }
        }
        if (!$request->wantsJson())
        {
            if ($e instanceof ValidationException) {
                return response($e->errors());
            }
        }

        return parent::render($request, $e);
    }
    
    private function customErrors()
    {
        return [
            \Illuminate\Database\QueryException::class => [
                'title' => 'Cannot connect to MySQL',
                'message' => 'Make sure MySQL service <code>is running</code> and MySQL connection settings in <code>.env</code> and <code>bootstrap/cache/config.php</code> files are correct',
                'match' => 'SQLSTATE[HY000] [2002] No such file or directory (SQL: select * from `m_users`',
                'notify' => false,
            ],
            \RuntimeException::class => [
                'match' => 'The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.',
                'title' => 'File missing',
                'message' => 'The <code>.env</code> file is missing. Make sure you have uploaded the file to the server',
                'notify' => false,
            ],
            \UnexpectedValueException::class => [
                'match' => 'failed to open stream: Permission denied',
                'title' => 'File system permission',
                'message' => function($exception) {
                    return 'Failed to write to file system, please make sure the application source folder is writable. Error: ' . $exception->getMessage();
                },
                'notify' => true,
            ],
        ];
    }

    private function getFormikValidationErrors(ValidationException $exception)
    {
        $original_errors = $exception->errors();
        $formatted_errors = [];
        foreach ($original_errors as $original_error_key => $original_error) {
            $formatted_error_key = $this->convertNumericalDotsToSquareBrackets($original_error_key);
            $formatted_errors[$formatted_error_key] = $original_error;
        }

        return $formatted_errors;
    }

    private function convertNumericalDotsToSquareBrackets($input)
    {
        $parts = explode('.', $input);
        $output = '';
        foreach ($parts as $key => $part) {
            if ($key === 0) {
                $output .= $part;
                continue;
            }

            if (is_numeric($part))
                $output .= '[' . $part . ']';
            else
                $output .= '.' . $part;
        }
        return $output;
    }
}
