<?php

namespace Accompli\Chrono\Test;

use Accompli\Chrono\Process\ProcessExecutor;
use PHPUnit_Framework_TestCase;

/**
 * ProcessExecutorTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class ProcessExecutorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test if ProcessExecutor::setWorkingDirectory sets the property.
     */
    public function testSetWorkingDirectory()
    {
        $processExecutor = new ProcessExecutor();
        $processExecutor->setWorkingDirectory(__DIR__);

        $this->assertAttributeSame(__DIR__, 'workingDirectory', $processExecutor);
    }

    /**
     * Tests if ProcessExecutor::getWorkingDirectory returns the current working directory when the working directory has not been set.
     */
    public function testGetWorkingDirectoryReturnsCurrentWorkingDirectory()
    {
        $processExecutor = new ProcessExecutor();

        $this->assertAttributeSame(null, 'workingDirectory', $processExecutor);
        $this->assertSame(realpath(__DIR__.'/../../'), $processExecutor->getWorkingDirectory());
    }

    /**
     * Tests if ProcessExecutor::getWorkingDirectory returns the working directory that has been set through ProcessExecutor::setWorkingDirectory.
     */
    public function testGetWorkingDirectoryReturnsSetWorkingDirectory()
    {
        $processExecutor = new ProcessExecutor();
        $processExecutor->setWorkingDirectory(__DIR__);

        $this->assertSame(__DIR__, $processExecutor->getWorkingDirectory());
    }

    /**
     * Tests if ProcessExecutor::execute returns the expected result.
     */
    public function testExecute()
    {
        $processExecutor = new ProcessExecutor();
        $processExecutionResult = $processExecutor->execute('echo test');

        $this->assertInstanceOf('Accompli\Chrono\Process\ProcessExecutionResult', $processExecutionResult);
        $this->assertSame(0, $processExecutionResult->getExitCode());
        $this->assertSame('test'.PHP_EOL, $processExecutionResult->getOutput());
        $this->assertSame(null, $processExecutionResult->getErrorOutput());
    }
}
