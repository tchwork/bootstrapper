<?php

namespace Tchwork\Bootstrapper\Symfony\Component\Console\Input;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;

final class InputInterfaceSingleton
{
    private static $input;

    public static function get(): InputInterface
    {
        if (self::$input) {
            return self::$input;
        }

        $input = new ArgvInput();

        if (null !== $env = $input->getParameterOption(['--env', '-e'], null, true)) {
            putenv('APP_ENV='.$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $env);
        }

        if ($input->hasParameterOption('--no-debug', true)) {
            putenv('APP_DEBUG='.$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '0');
        }

        return self::$input = $input;
    }
}
