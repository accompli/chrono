<?php

namespace Accompli\Chrono\Adapter;

use Accompli\Chrono\Process\ProcessExecutorInterface;

/**
 * AbstractAdapter.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * The URL of the repository.
     *
     * @var string
     */
    protected $repositoryUrl;

    /**
     * The working directory of the repository.
     *
     * @var string
     */
    protected $repositoryDirectory;

    /**
     * The process executor instance handling execution of commands.
     *
     * @var ProcessExecutorInterface
     */
    protected $processExecutor;

    /**
     * Constructs a new AbstractAdapter.
     *
     * @param string                   $repositoryUrl
     * @param string                   $repositoryDirectory
     * @param ProcessExecutorInterface $processExecutor
     */
    public function __construct($repositoryUrl, $repositoryDirectory, ProcessExecutorInterface $processExecutor)
    {
        $this->repositoryUrl = $repositoryUrl;
        $this->repositoryDirectory = $repositoryDirectory;
        $this->processExecutor = $processExecutor;
    }
}
