<?php

namespace Accompli\Chrono\Process;

/**
 * ExecutorInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface ProcessExecutorInterface
{
    /**
     * Executes the command.
     *
     * @param string $command
     *
     * @return ProcessExecutionResult
     */
    public function execute($command);
}
