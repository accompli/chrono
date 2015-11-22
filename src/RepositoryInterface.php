<?php

namespace Accompli\Chrono;

/**
 * RepositoryInterface.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
interface RepositoryInterface
{
    /**
     * Returns the list of branches from the repository.
     *
     * @return array
     */
    public function getBranches();

    /**
     * Returns the list of tags from the repository.
     *
     * @return array
     */
    public function getTags();

    /**
     * Checks out a branch or tag from the repository.
     *
     * @param string $version
     *
     * @return bool
     */
    public function checkout($version);
}
