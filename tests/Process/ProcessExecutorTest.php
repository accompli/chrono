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
     * Tests if ProcessExecutor::isDirectory returns true when the supplied path is a directory.
     */
    public function testIsDirectoryReturnsTrueForExistingDirectory()
    {
        $processExecutor = new ProcessExecutor();

        $this->assertTrue($processExecutor->isDirectory(__DIR__));
    }

    /**
     * Tests if ProcessExecutor::isDirectory returns false when the supplied path is a non-existing directory.
     */
    public function testIsDirectoryReturnsFalseForNonExistingDirectory()
    {
        $processExecutor = new ProcessExecutor();

        $this->assertFalse($processExecutor->isDirectory(__DIR__.'/non-existing'));
    }

    /**
     * Tests if ProcessExecutor::isDirectory returns false when the supplied path is a file.
     */
    public function testIsDirectoryReturnsFalseForFile()
    {
        $processExecutor = new ProcessExecutor();

        $this->assertFalse($processExecutor->isDirectory(__FILE__));
    }

    /**
     * Tests if ProcessExecutor::getWorkingDirectory returns the current working directory when the working directory has not been set.
     */
    public function testGetWorkingDirectoryReturnsCurrentWorkingDirectory()
    {
        $processExecutor = new ProcessExecutor();

        $this->assertSame(realpath(__DIR__.'/../../'), $processExecutor->getWorkingDirectory());
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
        $this->assertEquals('', $processExecutionResult->getErrorOutput());
    }
}
