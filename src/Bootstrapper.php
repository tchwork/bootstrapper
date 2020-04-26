<?php

namespace Tchwork\Bootstrapper;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Tchwork\Bootstrapper\Symfony\Component\Console\Input\InputInterfaceSingleton;

final class Bootstrapper implements BootstrapperInterface
{
    public function boot(string $projectDir): void
    {
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

    public function getRuntime(\Closure $closure): array
    {
        $arguments = [];

        foreach ((new \ReflectionFunction($closure))->getParameters() as $parameter) {
            $class = 'Tchwork\Bootstrapper\\'.$parameter->getType()->getName().'Singleton';

            if (class_exists($class)) {
                $arguments[] = $class::get();
                continue;
            }

            if ('Tchwork\Bootstrapper\arraySingleton' === $class) {
                $arguments[] = $_SERVER;
                continue;
            }

            $arguments[] = null;
        }

        return [$closure, $arguments];
    }

    public function getHandler(object $result, \Closure $closure, array $arguments): callable
    {
        $class = \get_class($result);

        if (class_exists($c = 'Tchwork\Bootstrapper\\'.$class.'Handler')) {
            return [$c, 'handle'];
        }

        foreach (class_parents($class) as $c) {
            if (class_exists($c = 'Tchwork\Bootstrapper\\'.$c.'Handler')) {
                return [$c, 'handle'];
            }
        }

        foreach (class_implements($class) as $c) {
            if (class_exists($c = 'Tchwork\Bootstrapper\\'.$c.'Handler')) {
                return [$c, 'handle'];
            }
        }

        return static function (object $result): int {
            echo 'The runtime returned an unsupported value of type '.get_debug_type($result).".\n";

            return 1;
        };
    }
}
