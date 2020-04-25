<?php

use Tchwork\Bootstrapper\Bootstrapper;

if (true !== require_once dirname(__DIR__, 2).'/autoload.php') {
    exit((new Bootstrapper(dirname(__DIR__, 3)))->handle(require $_SERVER['SCRIPT_FILENAME']));
}
