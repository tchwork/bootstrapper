<?php

namespace Tchwork\Bootstrapper\Symfony\Component\HttpKernel;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Tchwork\Bootstrapper\Symfony\Component\HttpFoundation\RequestSingleton;

final class HttpKernelInterfaceHandler
{
    public static function handle(HttpKernelInterface $kernel): int
    {
        $request = RequestSingleton::get();
        $response = $kernel->handle($request);
        $response->send();

        if ($kernel instanceof TerminableInterface) {
            $kernel->terminate($request, $response);
        }

        return 0;
    }
}
