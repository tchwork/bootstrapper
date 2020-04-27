<?php

use Tchwork\Bootstrapper\SymfonyBootstrapper;

if (!isset($_SERVER['TCHWORK_BOOTSTRAPPER'])) {
    $_SERVER['TCHWORK_BOOTSTRAPPER'] = SymfonyBootstrapper::class;
}

require __DIR__.'/bootstrap.php';
