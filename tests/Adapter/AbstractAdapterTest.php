<?php

namespace Accompli\Chrono\Test;

use Accompli\Chrono\Adapter\AbstractAdapter;
use Accompli\Chrono\Process\ProcessExecutorInterface;
use PHPUnit_Framework_TestCase;

/**
 * AbstractAdapterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class AbstractAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if construction of AbstractAdapter sets the properties.
     */
    public function testConstruct()
    {
        $repositoryUrl = 'https://github.com/accompli/chrono.git';
        $repositoryDirectory = __DIR__;
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();

        $abstractAdapter = $this->getMockBuilder(AbstractAdapter::class)
                ->setConstructorArgs(array($repositoryUrl, $repositoryDirectory, $processExecutorMock))
                ->getMockForAbstractClass();

        $this->assertAttributeSame($repositoryUrl, 'repositoryUrl', $abstractAdapter);
        $this->assertAttributeSame($repositoryDirectory, 'repositoryDirectory', $abstractAdapter);
        $this->assertAttributeSame($processExecutorMock, 'processExecutor', $abstractAdapter);
    }
}
