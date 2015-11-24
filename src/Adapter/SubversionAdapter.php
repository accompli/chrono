<?php

namespace Accompli\Chrono\Adapter;

use Symfony\Component\Process\ProcessUtils;

/**
 * SubversionAdapter.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class SubversionAdapter extends AbstractAdapter
{
    /**
     * The path in the repository representing 'trunk' or 'master'.
     *
     * @var string
     */
    private $trunkPath = 'trunk';

    /**
     * The path in the repository representing 'branches'.
     *
     * @var string
     */
    private $branchesPath = 'branches';

    /**
     * The path in the repository representing 'tags'.
     *
     * @var string
     */
    private $tagsPath = 'tags';

    /**
     * {@inheritdoc}
     */
    public function supportsRepository()
    {
        $result = $this->processExecutor->execute('svn --version');
        if ($result->isSuccessful() === false) {
            return false;
        }

        if (preg_match('#(^svn://|^svn\+ssh://|svn\.)#i', $this->repositoryUrl)) {
            return true;
        }

        $result = $this->processExecutor->execute(sprintf('svn info --non-interactive %s', ProcessUtils::escapeArgument($this->repositoryUrl)));
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

        $result = $this->processExecutor->execute(sprintf('svn ls --non-interactive %s', ProcessUtils::escapeArgument($this->repositoryUrl.'/'.$this->trunkPath)));
        if ($result->isSuccessful()) {
            foreach ($result->getOutputAsArray() as $branch) {
                $matches = array();
                if (preg_match('#^\s*(\S+).*?(\S+)\s*$#', $branch, $matches) && $matches[2] === './') {
                    $branches[$matches[1]] = 'master';
                }
            }
        }

        $result = $this->processExecutor->execute(sprintf('svn ls --non-interactive %s', ProcessUtils::escapeArgument($this->repositoryUrl.'/'.$this->branchesPath)));
        if ($result->isSuccessful()) {
            foreach ($result->getOutputAsArray() as $branch) {
                $matches = array();
                if (preg_match('#^\s*(\S+).*?(\S+)\s*$#', $branch, $matches)) {
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

        $result = $this->processExecutor->execute(sprintf('svn ls --non-interactive %s', ProcessUtils::escapeArgument($this->repositoryUrl.'/'.$this->tagsPath)));
        if ($result->isSuccessful()) {
            foreach ($result->getOutputAsArray() as $tag) {
                $matches = array();
                if (preg_match('#^\s*(\S+).*?(\S+)\s*$#', $tag, $matches)) {
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

        $escapedRepositoryUrlWithVersionPath = $this->getRepositoryUrlWithVersionPath($version);
        if ($escapedRepositoryUrlWithVersionPath === false) {
            return false;
        }

        if ($this->processExecutor->isDirectory($this->repositoryDirectory) && $this->processExecutor->execute('svn info --non-interactive', $this->repositoryDirectory)->isSuccessful()) {
            $checkoutSuccesful = $this->processExecutor->execute(sprintf('svn switch --non-interactive %s', $escapedRepositoryUrlWithVersionPath), $this->repositoryDirectory)->isSuccessful();
        } else {
            $escapedRepositoryDirectory = ProcessUtils::escapeArgument($this->repositoryDirectory);

            $result = $this->processExecutor->execute(sprintf('svn checkout --non-interactive %s %s', $escapedRepositoryUrlWithVersionPath, $escapedRepositoryDirectory));
            $checkoutSuccesful = $result->isSuccessful();
        }

        return $checkoutSuccesful;
    }

    /**
     * Returns the escaped repository URL with version path.
     * Returns false when the repository URL for a version cannot be found.
     *
     * @param string $version
     *
     * @return string|bool
     */
    private function getRepositoryUrlWithVersionPath($version)
    {
        $repositoryUrl = $this->repositoryUrl;
        if (in_array($version, array('master', $this->trunkPath))) {
            $repositoryUrl .= '/'.$this->trunkPath;
        } elseif (in_array($version, $this->getBranches())) {
            $repositoryUrl .= '/'.$this->branchesPath.'/'.$version;
        } elseif (in_array($version, $this->getTags())) {
            $repositoryUrl .= '/'.$this->tagsPath.'/'.$version;
        } else {
            return false;
        }

        return ProcessUtils::escapeArgument($repositoryUrl);
    }
}
