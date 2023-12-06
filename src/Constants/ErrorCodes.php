<?php

namespace Superzc\QQConnect\Constants;

class ErrorCodes
{
    const ERROR               = -10000;
    const INVALID_PARAMS      = -10001;
    const SERVICE_UNAVAILABLE = -10002;

    public static function getMessage($code)
    {
        $messages = [
            self::ERROR => '',
            self::INVALID_PARAMS => '',
            self::SERVICE_UNAVAILABLE => '',
            // 更多的错误码和消息...
        ];

        return $messages[$code] ?? 'Error';
    }
}