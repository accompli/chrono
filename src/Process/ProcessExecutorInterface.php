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
     * Returns true if the suppied path is a directory.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isDirectory($path);

    /**
     * Executes the command.
     *
     * @param string      $command
     * @param string|null $workingDirectory
     *
     * @return ProcessExecutionResult
     */
    public function execute($command, $workingDirectory = null);

    /**
     * Returns the ProcessExecutionResult instance of the last executed command.
     *
     * @return ProcessExecutionResult
     */
    public function getLastProcessExecutionResult();
}
