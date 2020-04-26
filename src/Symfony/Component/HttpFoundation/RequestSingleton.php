<?php

namespace Tchwork\Bootstrapper\Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Request;

final class RequestSingleton
{
    private static $request;

    public static function get(): Request
    {
        return self::$request ?? self::$request = Request::createFromGlobals();
    }
}
