<?php

namespace Tchwork\Bootstrapper\Symfony\Component\Console;

use Symfony\Component\Console\Application;

/**
 * @internal
 */
class ApplicationHandler
{
    public static function handle(Application $app): int
    {
        if (!\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
            echo 'Warning: The console should be invoked via the CLI version of PHP, not the '.\PHP_SAPI.' SAPI'.\PHP_EOL;
        }

        set_time_limit(0);

        return $app->run(Input\InputInterfaceSingleton::get(), Output\OutputInterfaceSingleton::get());
    }
}
