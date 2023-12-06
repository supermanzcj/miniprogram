<?php

namespace Superzc\Miniprogram\Exceptions;

use Exception;
use Illuminate\Http\Request;

class DefaultException extends Exception
{
    // 重定义异常捕获时的response
    public function render(Request $request)
    {
        return response()->json([
            'ret' => $this->getCode(),
            'msg' => $this->getMessage(),
        ], 400);
    }
}