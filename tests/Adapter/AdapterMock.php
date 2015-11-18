<?php

namespace Accompli\Chrono\Test\Adapter;

use Accompli\Chrono\Adapter\AdapterInterface;

/**
 * AdapterMock.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class AdapterMock implements AdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsRepository()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getBranches()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function checkout($version)
    {
    }
}
