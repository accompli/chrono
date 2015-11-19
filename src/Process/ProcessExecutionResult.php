<?php

namespace Accompli\Chrono\Process;

/**
 * ProcessExecutionResult.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ProcessExecutionResult
{
    /**
     * The exit code of the process execution.
     *
     * @var int
     */
    private $exitCode;

    /**
     * The output of the process execution.
     *
     * @var string
     */
    private $output;

    /**
     * The error output of the process execution.
     *
     * @var string
     */
    private $errorOutput;

    /**
     * Constructs a new ProcessExecutionResult.
     *
     * @param int    $exitCode
     * @param string $output
     * @param string $errorOutput
     */
    public function __construct($exitCode, $output, $errorOutput)
    {
        $this->exitCode = $exitCode;
        $this->output = $output;
        $this->errorOutput = $errorOutput;
    }

    /**
     * Returns true when the process execution was successful.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return ($this->exitCode === 0);
    }

    /**
     * Returns the exit code.
     *
     * @return int
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * Returns the output.
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Returns the error output.
     *
     * @return string|null
     */
    public function getErrorOutput()
    {
        return $this->errorOutput;
    }
}
