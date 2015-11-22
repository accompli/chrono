<?php

namespace Accompli\Chrono;

use Accompli\Chrono\Adapter\AdapterInterface;
use Accompli\Chrono\Process\ProcessExecutorInterface;
use InvalidArgumentException;

/**
 * Repository.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class Repository implements RepositoryInterface
{
    /**
     * The URL of the repository.
     *
     * @var string
     */
    private $repositoryUrl;

    /**
     * The working directory of the repository.
     *
     * @var string
     */
    private $repositoryDirectory;

    /**
     * The process executor instance handling execution of commands.
     *
     * @var ProcessExecutorInterface
     */
    private $processExecutor;

    /**
     * The array with available VCS adapters.
     *
     * @var array
     */
    private $adapters = array(
    );

    /**
     * The active VCS adapter for the repository.
     *
     * @var AdapterInterface
     */
    private $repositoryAdapter;

    /**
     * Constructs a new Repository.
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

    /**
     * {@inheritdoc}
     */
    public function getBranches()
    {
        $this->initialize();

        return $this->repositoryAdapter->getBranches();
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        $this->initialize();

        return $this->repositoryAdapter->getTags();
    }

    /**
     * Returns the supported adapter for the repository.
     *
     * @return AdapterInterface|null
     */
    public function getAdapter()
    {
        if ($this->repositoryAdapter instanceof AdapterInterface) {
            return $this->repositoryAdapter;
        }

        foreach ($this->adapters as $adapterClass) {
            if (class_exists($adapterClass) === false || in_array('Accompli\Chrono\Adapter\AdapterInterface', class_implements($adapterClass)) === false) {
                continue;
            }

            $adapter = new $adapterClass($this->repositoryUrl, $this->repositoryDirectory, $this->processExecutor);
            if ($adapter->supportsRepository()) {
                return $adapter;
            }
        }
    }

    /**
     * Sets an array of available VCS adapter classes.
     *
     * @param array $adapters
     */
    public function setAdapters(array $adapters)
    {
        $this->adapters = $adapters;
    }

    /**
     * {@inheritdoc}
     */
    public function checkout($version)
    {
        $this->initialize();

        return $this->repositoryAdapter->checkout($version);
    }

    /**
     * Initializes the repository (adapter).
     *
     * @throws InvalidArgumentException
     */
    private function initialize()
    {
        $adapter = $this->getAdapter();
        if (($adapter instanceof AdapterInterface) === false) {
            throw new InvalidArgumentException(sprintf('No adapter found to handle VCS repository "%s".', $this->repositoryUrl));
        }

        $this->repositoryAdapter = $adapter;
    }
}
