<?php

namespace Tchwork\Bootstrapper;

interface BootstrapperInterface
{
    /**
     * Prepares the PHP engine.
     */
    public function boot(string $projectDir): void;

    /**
     * Returns a closure and its arguments; they will run the main code.
     */
    public function getRuntime(\Closure $closure): array;

    /**
     * Returns a handler that can deal with the result provided by the runtime.
     */
    public function getHandler(object $result, \Closure $closure, array $arguments): callable;
}
