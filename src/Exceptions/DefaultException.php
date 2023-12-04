<?php

namespace Superzc\Miniprogram\Exceptions;

use Exception;

class DefaultException extends Exception
{
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }

    // 重定义异常捕获时的response
    public function render(Request $request)
    {
        return response()->json([
            'code' => 1,
            'message' => $this->getMessage(),
        ], 400);
    }
}