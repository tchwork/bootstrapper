<?php

use Tchwork\Bootstrapper\Bootstrapper;

if (true === require_once dirname(__DIR__, 2).'/autoload.php') {
    return;
}

if (!isset($_SERVER['APP_BOOTSTRAPPER'])) {
    $_SERVER['APP_BOOTSTRAPPER'] = new Bootstrapper();
} elseif (is_string($_SERVER['APP_BOOTSTRAPPER'])) {
    $_SERVER['APP_BOOTSTRAPPER'] = new $_SERVER['APP_BOOTSTRAPPER']();
}

if (1 === $closure = require $_SERVER['SCRIPT_FILENAME']) {
    exit(0);
}

[$closure, $arguments] = $_SERVER['APP_BOOTSTRAPPER']->getRuntime($closure);
$result = $closure(...$arguments);
$closure = $_SERVER['APP_BOOTSTRAPPER']->getHandler($result, $closure, $arguments);

exit($closure($result));
