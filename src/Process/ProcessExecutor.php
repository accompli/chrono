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
    public function execute($command, $workingDirectory = null)
    {
        if ($workingDirectory === null) {
            $workingDirectory = $this->getWorkingDirectory();
        }

        $process = new Process($command, $workingDirectory);
        $process->setTimeout(300);
        $process->run();

        return new ProcessExecutionResult($process->getExitCode(), $process->getOutput(), $process->getErrorOutput());
    }
}
