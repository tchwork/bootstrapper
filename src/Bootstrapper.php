<?php

namespace Tchwork\Bootstrapper;

class Bootstrapper implements BootstrapperInterface
{
    public function getRuntime(\Closure $closure): array
    {
        $arguments = [];

        foreach ((new \ReflectionFunction($closure))->getParameters() as $parameter) {
            $class = 'Tchwork\Bootstrapper\\'.$parameter->getType()->getName().'Singleton';

            if ('Tchwork\Bootstrapper\arraySingleton' === $class) {
                $arguments[] = $_SERVER;
                continue;
            }

            if (class_exists($class)) {
                $arguments[] = $class::get();
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
            echo 'The runtime returned an unsupported value of type '.$class.".\n";

            return 1;
        };
    }
}
