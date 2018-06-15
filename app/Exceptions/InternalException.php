<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use function response;
use Throwable;
use function view;

class InternalException extends Exception
{
    protected $msg_for_user;

    public function __construct(string $message = "", string $msg_for_user = '系统内部错误', int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->msg_for_user = $msg_for_user;
    }

    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(['msg' => $this->msg_for_user], $this->code);
        }
        return view('pages.error', ['msg' => $this->msg_for_user]);

    }
}
