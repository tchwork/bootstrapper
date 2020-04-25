<?php

namespace Tchwork\Bootstrapper;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Tchwork\Bootstrapper\Symfony\Component\Console\Input\InputInterfaceSingleton;

/**
 * @internal
 */
final class Bootstrapper
{
    public function __construct(string $projectDir)
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

    public function handle($main): int
    {
        if (1 === $main) {
            return 0;
        }

        if (\is_callable($main)) {
            if (!$main instanceof \Closure) {
                $main = \Closure::fromCallable($main);
            }

            $arguments = [];
            foreach ((new \ReflectionFunction($main))->getParameters() as $parameter) {
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

            $main = $main(...$arguments);
        }

        if (!\is_object($main)) {
            echo 'Main script returned unsupported value of type '.get_debug_type($main).".\n";

            return 1;
        }

        $class = \get_class($main);

        if (class_exists($c = 'Tchwork\Bootstrapper\\'.$class.'Handler')) {
            return $c::handle($main);
        }

        foreach (class_parents($class) as $c) {
            if (class_exists($c = 'Tchwork\Bootstrapper\\'.$c.'Handler')) {
                return $c::handle($main);
            }
        }

        foreach (class_implements($class) as $c) {
            if (class_exists($c = 'Tchwork\Bootstrapper\\'.$c.'Handler')) {
                return $c::handle($main);
            }
        }

        echo 'Main script returned unsupported value of type '.get_debug_type($main).".\n";

        return 1;
    }
}
