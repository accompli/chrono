<?php

namespace Accompli\Chrono\Adapter;

use Symfony\Component\Process\ProcessUtils;

/**
 * GitAdapter.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class GitAdapter extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function supportsRepository()
    {
        $result = $this->processExecutor->execute('git --version');
        if ($result->isSuccessful() === false) {
            return false;
        }

        if (preg_match('#(^git://|\.git$|git(?:olite)?@|//git\.|//github.com/)#i', $this->repositoryUrl) === 1) {
            return true;
        }

        $result = $this->processExecutor->execute(sprintf('git ls-remote --heads %s', ProcessUtils::escapeArgument($this->repositoryUrl)), null, $this->getEnvironmentVariables());
        if ($result->isSuccessful()) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getBranches()
    {
        $branches = array();

        $result = $this->processExecutor->execute(sprintf('git ls-remote --heads %s', ProcessUtils::escapeArgument($this->repositoryUrl)), null, $this->getEnvironmentVariables());
        if ($result->isSuccessful()) {
            foreach ($result->getOutputAsArray() as $branch) {
                $matches = array();
                if (preg_match('#^([a-f0-9]{40})\s+refs\/heads\/(\S+)$#', $branch, $matches) === 1) {
                    $branches[$matches[1]] = $matches[2];
                }
            }
        }

        return $branches;
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        $tags = array();

        $result = $this->processExecutor->execute(sprintf('git ls-remote --tags %s', ProcessUtils::escapeArgument($this->repositoryUrl)), null, $this->getEnvironmentVariables());
        if ($result->isSuccessful()) {
            foreach ($result->getOutputAsArray() as $tag) {
                $matches = array();
                if (preg_match('#^([a-f0-9]{40})\s+refs\/tags\/([\S]+)\^{}$#', $tag, $matches) === 1) {
                    $tags[$matches[1]] = $matches[2];
                }
            }
        }

        return $tags;
    }

    /**
     * {@inheritdoc}
     */
    public function checkout($version)
    {
        $checkoutSuccesful = false;

        $escapedVersion = ProcessUtils::escapeArgument($version);
        if ($this->processExecutor->isDirectory($this->repositoryDirectory) && $this->processExecutor->execute('git rev-parse --is-inside-work-tree', $this->repositoryDirectory)->isSuccessful()) {
            $commands = array(
                'git fetch',
                sprintf('git checkout %s', $escapedVersion),
            );

            $checkoutSuccesful = true;
            foreach ($commands as $command) {
                $result = $this->processExecutor->execute($command, $this->repositoryDirectory, $this->getEnvironmentVariables());
                if ($result->isSuccessful() === false) {
                    $checkoutSuccesful = false;

                    break;
                }
            }

            $result = $this->processExecutor->execute('git branch', $this->repositoryDirectory, $this->getEnvironmentVariables());

            $checkoutSuccesful = $checkoutSuccesful && $result->isSuccessful();
            if (preg_match('#^ \*.*(?:no branch|HEAD detached at)#', $result->getOutput()) === 0) {
                $checkoutSuccesful = $checkoutSuccesful && $this->processExecutor->execute('git pull', $this->repositoryDirectory, $this->getEnvironmentVariables())->isSuccessful();
            }
        } else {
            $escapedRepositoryUrl = ProcessUtils::escapeArgument($this->repositoryUrl);
            $escapedRepositoryDirectory = ProcessUtils::escapeArgument($this->repositoryDirectory);

            $result = $this->processExecutor->execute(sprintf('git clone -b %s --single-branch %s %s', $escapedVersion, $escapedRepositoryUrl, $escapedRepositoryDirectory), null, $this->getEnvironmentVariables());
            $checkoutSuccesful = $result->isSuccessful();
        }

        return $checkoutSuccesful;
    }

    /**
     * Returns the environment variables for Git commands.
     *
     * @return array
     */
    private function getEnvironmentVariables()
    {
        return array(
            'GIT_TERMINAL_PROMPT' => '0',
            'GIT_ASKPASS' => 'echo',
        );
    }
}
