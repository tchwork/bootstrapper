<?php

namespace Tchwork\Bootstrapper\Symfony\Component\Console\Output;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class OutputInterfaceSingleton
{
    private static $output;

    public static function get(): OutputInterface
    {
        return self::$output ?? self::$output = new ConsoleOutput();
    }
}
