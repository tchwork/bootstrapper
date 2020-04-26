<?php

namespace Tchwork\Bootstrapper\Symfony\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Response;

final class ResponseHandler
{
    public static function handle(Response $response): int
    {
        $response->send();

        return 0;
    }
}
