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
     * The path to the execution working directory.
     *
     * @var string
     */
    private $workingDirectory;

    /**
     * Returns the path to the execution working directory.
     *
     * @return string
     */
    public function getWorkingDirectory()
    {
        if ($this->workingDirectory === null) {
            return getcwd();
        }

        return $this->workingDirectory;
    }

    /**
     * Sets the path to the execution working directory.
     *
     * @param string $workingDirectory
     */
    public function setWorkingDirectory($workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($command)
    {
        $process = new Process($command, $this->getWorkingDirectory());
        $process->run();

        return new ProcessExecutionResult($process->getExitCode(), $process->getOutput(), $process->getErrorOutput());
    }
}
