<?php

namespace Accompli\Chrono\Process;

use Symfony\Component\Process\Process;

/**
 * ProcessExecutor.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ProcessExecutor implements ProcessExecutorInterface
{
    /**
     * The ProcessExecutionResult instance of the last executed command.
     *
     * @var ProcessExecutionResult
     */
    private $lastProcessExecutionResult;

    /**
     * {@inheritdoc}
     */
    public function isDirectory($path)
    {
        return is_dir($path);
    }

    /**
     * Returns the current working directory.
     *
     * @return string
     */
    public function getWorkingDirectory()
    {
        return getcwd();
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command, $workingDirectory = null, array $environmentVariables = null)
    {
        if ($workingDirectory === null) {
            $workingDirectory = $this->getWorkingDirectory();
        }

        $process = new Process($command, $workingDirectory, $environmentVariables);
        $process->setTimeout(300);
        $process->run();

        $this->lastProcessExecutionResult = new ProcessExecutionResult($process->getExitCode(), $process->getOutput(), $process->getErrorOutput());

        return $this->lastProcessExecutionResult;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastProcessExecutionResult()
    {
        return $this->lastProcessExecutionResult;
    }
}
