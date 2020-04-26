<?php

use Tchwork\Bootstrapper\Bootstrapper;

if (true === require_once dirname(__DIR__, 2).'/autoload.php') {
    return;
}

$bootstrapper = new Bootstrapper();
$bootstrapper->boot(dirname(__DIR__, 3));
$closure = require $_SERVER['SCRIPT_FILENAME'];
[$closure, $arguments] = $bootstrapper->getRuntime($closure);
$result = $closure(...$arguments);
$closure = $bootstrapper->getHandler($result, $closure, $arguments);

exit($closure($result));
