<?php

namespace Accompli\Chrono\Test;

use Accompli\Chrono\Adapter\AdapterInterface;
use Accompli\Chrono\Process\ProcessExecutor;
use Accompli\Chrono\Process\ProcessExecutorInterface;
use Accompli\Chrono\Repository;
use Accompli\Chrono\Test\Adapter\AdapterMock;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;

/**
 * RepositoryTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class RepositoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if construction of Repository sets the properties.
     */
    public function testConstruct()
    {
        $repositoryUrl = 'https://github.com/accompli/chrono.git';
        $repositoryDirectory = __DIR__;
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)->getMock();

        $repository = new Repository($repositoryUrl, $repositoryDirectory, $processExecutorMock);

        $this->assertAttributeSame($repositoryUrl, 'repositoryUrl', $repository);
        $this->assertAttributeSame($repositoryDirectory, 'repositoryDirectory', $repository);
        $this->assertAttributeSame($processExecutorMock, 'processExecutor', $repository);
    }

    /**
     * Tests if Repository::setAdapters sets the adapters property.
     */
    public function testSetAdapters()
    {
        $repositoryUrl = 'https://github.com/accompli/chrono.git';
        $repositoryDirectory = __DIR__;
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)->getMock();

        $repository = new Repository($repositoryUrl, $repositoryDirectory, $processExecutorMock);
        $repository->setAdapters(array(AdapterMock::class));

        $this->assertAttributeSame(array(AdapterMock::class), 'adapters', $repository);
    }

    /**
     * Tests if Repository::getAdapter returns an adapter instance.
     */
    public function testGetAdapter()
    {
        $repositoryUrl = 'https://github.com/accompli/chrono.git';
        $repositoryDirectory = __DIR__;
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)->getMock();

        $repository = new Repository($repositoryUrl, $repositoryDirectory, $processExecutorMock);
        $repository->setAdapters(array(AdapterMock::class));

        $this->assertInstanceOf(AdapterMock::class, $repository->getAdapter());
    }

    /**
     * Tests if Repository::getAdapter returns null when the adapter class does not exist.
     */
    public function testGetAdapterReturnsNullWhenClassDoesNotExist()
    {
        $repositoryUrl = 'https://github.com/accompli/chrono.git';
        $repositoryDirectory = __DIR__;
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)->getMock();

        $repository = new Repository($repositoryUrl, $repositoryDirectory, $processExecutorMock);
        $repository->setAdapters(array('DoesNotExistAdapter'));

        $this->assertNull($repository->getAdapter());
    }

    /**
     * Tests if Repository::getAdapter returns null when class is not an adapter implementing the AdapterInterface.
     */
    public function testGetAdapterReturnsNullWhenClassIsNoAdapter()
    {
        $repositoryUrl = 'https://github.com/accompli/chrono.git';
        $repositoryDirectory = __DIR__;
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)->getMock();

        $repository = new Repository($repositoryUrl, $repositoryDirectory, $processExecutorMock);
        $repository->setAdapters(array(ProcessExecutor::class));

        $this->assertNull($repository->getAdapter());
    }

    /**
     * Tests if Repository::getBranches calls getBranches on the adapter and returns an array.
     */
    public function testGetBranches()
    {
        $repositoryUrl = 'https://github.com/accompli/chrono.git';
        $repositoryDirectory = __DIR__;
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();

        $adapterMock = $this->getMockBuilder(AdapterInterface::class)
                ->getMock();
        $adapterMock->expects($this->once())
                ->method('getBranches')
                ->willReturn(array());

        $repository = $this->getMockBuilder(Repository::class)
                ->setConstructorArgs(array($repositoryUrl, $repositoryDirectory, $processExecutorMock))
                ->setMethods(array('getAdapter'))
                ->getMock();

        $repository->expects($this->once())
                ->method('getAdapter')
                ->willReturn($adapterMock);

        $this->assertInternalType('array', $repository->getBranches());
    }

    /**
     * Tests if Repository::getTags calls getTags on the adapter and returns an array.
     */
    public function testGetTags()
    {
        $repositoryUrl = 'https://github.com/accompli/chrono.git';
        $repositoryDirectory = __DIR__;
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();

        $adapterMock = $this->getMockBuilder(AdapterInterface::class)
                ->getMock();
        $adapterMock->expects($this->once())
                ->method('getTags')
                ->willReturn(array());

        $repository = $this->getMockBuilder(Repository::class)
                ->setConstructorArgs(array($repositoryUrl, $repositoryDirectory, $processExecutorMock))
                ->setMethods(array('getAdapter'))
                ->getMock();

        $repository->expects($this->once())
                ->method('getAdapter')
                ->willReturn($adapterMock);

        $this->assertInternalType('array', $repository->getTags());
    }

    /**
     * Tests if Repository::checkout calls checkout on the adapter.
     */
    public function testCheckout()
    {
        $repositoryUrl = 'https://github.com/accompli/chrono.git';
        $repositoryDirectory = __DIR__;
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();

        $adapterMock = $this->getMockBuilder(AdapterInterface::class)
                ->getMock();
        $adapterMock->expects($this->once())
                ->method('checkout')
                ->with($this->equalTo('1.0.0'))
                ->willReturn(array());

        $repository = $this->getMockBuilder(Repository::class)
                ->setConstructorArgs(array($repositoryUrl, $repositoryDirectory, $processExecutorMock))
                ->setMethods(array('getAdapter'))
                ->getMock();

        $repository->expects($this->once())
                ->method('getAdapter')
                ->willReturn($adapterMock);

        $repository->checkout('1.0.0');
    }

    /**
     * Tests if Repository::getAdapter always returns the same adapter instance after Repository::initialize has been called.
     */
    public function testGetAdapterAlwaysReturnsSameInstanceAfterInitialize()
    {
        $repositoryUrl = 'https://github.com/accompli/chrono.git';
        $repositoryDirectory = __DIR__;
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();

        $repository = new Repository($repositoryUrl, $repositoryDirectory, $processExecutorMock);
        $repository->setAdapters(array(AdapterMock::class));
        $repository->getTags();

        $adapter = $repository->getAdapter();
        $this->assertSame($adapter, $repository->getAdapter());
    }

    /**
     * Tests if Repository::initialize throws an InvalidArgumentException when no supported adapter is available.
     */
    public function testInitializeThrowsInvalidArgumentExceptionWhenNoSupportedAdapterAvailable()
    {
        $repositoryUrl = 'https://github.com/accompli/chrono.git';
        $repositoryDirectory = __DIR__;
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();

        $repository = new Repository($repositoryUrl, $repositoryDirectory, $processExecutorMock);
        $repository->setAdapters(array());

        $this->setExpectedException(InvalidArgumentException::class, 'No adapter found to handle VCS repository "https://github.com/accompli/chrono.git".');

        $repository->getTags();
    }
}
