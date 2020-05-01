<?php

use Tchwork\Bootstrapper\SymfonyBootstrapper;

if (!isset($_SERVER['APP_BOOTSTRAPPER'])) {
    $_SERVER['APP_BOOTSTRAPPER'] = SymfonyBootstrapper::class;
}

require __DIR__.'/bootstrap.php';
