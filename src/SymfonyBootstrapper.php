<?php

namespace Tchwork\Bootstrapper;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Tchwork\Bootstrapper\Symfony\Component\Console\Input\InputInterfaceSingleton;
use Tchwork\Bootstrapper\Symfony\Component\HttpFoundation\RequestSingleton;

// Help opcache.preload discover always-needed symbols
class_exists(RequestSingleton::class);

if (!class_exists(Dotenv::class)) {
    throw new \LogicException(sprintf('You cannot use "%s" as the Dotenv component is not installed. Try running "composer require symfony/dotenv".', SymfonyBootstrapper::class));
}

if (!class_exists(ErrorHandler::class)) {
    throw new \LogicException(sprintf('You cannot use "%s" as the ErrorHandler component is not installed. Try running "composer require symfony/error-handler".', SymfonyBootstrapper::class));
}

class SymfonyBootstrapper extends Bootstrapper
{
    public function __construct(string $projectDir = null)
    {
        $projectDir = $projectDir ?? \dirname(__DIR__, 4);

        if (isset($_SERVER['argv']) && class_exists(ArgvInput::class)) {
            InputInterfaceSingleton::get();
        }

        (new Dotenv())->bootEnv($projectDir.'/.env');

        if ($_SERVER['APP_DEBUG']) {
            umask(0000);
            Debug::enable();
        } else {
            ErrorHandler::register();
        }
    }

    public function getHandler(object $result, \Closure $closure, array $arguments): callable
    {
        if ($result instanceof HttpKernelInterface) {
            return static function ($kernel) {
                $request = RequestSingleton::get();
                $response = $kernel->handle($request);
                $response->send();

                if ($kernel instanceof TerminableInterface) {
                    $kernel->terminate($request, $response);
                }

                return 0;
            };
        }

        if ($result instanceof Response) {
            return static function ($response) {
                $response->send();

                return 0;
            };
        }

        return parent::getHandler($result, $closure, $arguments);
    }
}
