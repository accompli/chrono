<?php

namespace Accompli\Chrono\Test;

use Accompli\Chrono\Adapter\GitAdapter;
use Accompli\Chrono\Process\ProcessExecutionResult;
use Accompli\Chrono\Process\ProcessExecutorInterface;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Process\ProcessUtils;

/**
 * GitAdapterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class GitAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if GitAdapter::supportsRepository returns the expected result.
     *
     * @dataProvider provideTestSupportsRepository
     *
     * @param string                   $repositoryUrl
     * @param ProcessExecutorInterface $processExecutor
     * @param bool                     $expectedResult
     */
    public function testSupportsRepository($repositoryUrl, ProcessExecutorInterface $processExecutor, $expectedResult)
    {
        $gitAdapter = new GitAdapter($repositoryUrl, '', $processExecutor);

        $this->assertSame($expectedResult, $gitAdapter->supportsRepository());
    }

    /**
     * Tests if GitAdapter::getBranches returns the expected result.
     *
     * @dataProvider provideTestGetBranches
     *
     * @param ProcessExecutorInterface $processExecutor
     * @param array                    $expectedResult
     */
    public function testGetBranches(ProcessExecutorInterface $processExecutor, array $expectedResult)
    {
        $gitAdapter = new GitAdapter('https://github.com/accompli/chrono.git', '', $processExecutor);

        $this->assertSame($expectedResult, $gitAdapter->getBranches());
    }

    /**
     * Tests if GitAdapter::getTags returns the expected result.
     *
     * @dataProvider provideTestGetTags
     *
     * @param ProcessExecutorInterface $processExecutor
     * @param array                    $expectedResult
     */
    public function testGetTags(ProcessExecutorInterface $processExecutor, array $expectedResult)
    {
        $gitAdapter = new GitAdapter('https://github.com/accompli/chrono.git', '', $processExecutor);

        $this->assertSame($expectedResult, $gitAdapter->getTags());
    }

    /**
     * Tests if GitAdapter::checkout returns the expected result.
     *
     * @dataProvider provideTestCheckout
     *
     * @param ProcessExecutorInterface $processExecutor
     * @param bool                     $expectedResult
     */
    public function testCheckout(ProcessExecutorInterface $processExecutor, $expectedResult)
    {
        $gitAdapter = new GitAdapter('https://github.com/accompli/chrono.git', '/git/working-directory', $processExecutor);

        $this->assertSame($expectedResult, $gitAdapter->checkout('0.1.0'));
    }

    /**
     * Returns the test data and expected results for testing GitAdapter::supportsRepository.
     *
     * @return array
     */
    public function provideTestSupportsRepository()
    {
        $provideTest = array();

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo('git --version'))
                ->willReturn(new ProcessExecutionResult(1, '', ''));
        $provideTest[] = array('https://github.com/accompli/chrono.git', $processExecutorMock, false);

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo('git --version'))
                ->willReturn(new ProcessExecutionResult(0, '', ''));
        $provideTest[] = array('https://github.com/accompli/chrono.git', $processExecutorMock, true);

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo('git --version'))
                ->willReturn(new ProcessExecutionResult(0, '', ''));
        $provideTest[] = array('git@github.com:accompli/chrono.git', $processExecutorMock, true);

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->exactly(2))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo('git --version')),
                        array($this->equalTo(sprintf('git ls-remote --heads %s', ProcessUtils::escapeArgument('user@example.com:accompli/chrono'))))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(0, '', '')
                );
        $provideTest[] = array('user@example.com:accompli/chrono', $processExecutorMock, true);

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->exactly(2))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo('git --version')),
                        array($this->equalTo(sprintf('git ls-remote --heads %s', ProcessUtils::escapeArgument('svn+ssh://example.com/accompli/chrono'))))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(1, '', '')
                );
        $provideTest[] = array('svn+ssh://example.com/accompli/chrono', $processExecutorMock, false);

        return $provideTest;
    }

    /**
     * Returns the test data and expected results for testing GitAdapter::getBranches.
     *
     * @return array
     */
    public function provideTestGetBranches()
    {
        $branchesCommand = sprintf('git ls-remote --heads %s', ProcessUtils::escapeArgument('https://github.com/accompli/chrono.git'));

        $provideTest = array();

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo($branchesCommand))
                ->willReturn(new ProcessExecutionResult(1, '', ''));
        $provideTest[] = array($processExecutorMock, array());

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo($branchesCommand))
                ->willReturn(new ProcessExecutionResult(0, "6f8ce5e29cdcd894d6b7aaeb590e656f9348ac3a        refs/heads/master\n162eb4911ffc5e30496b5ed6c40c5182c1fd22d8        refs/heads/1.0\n", ''));
        $provideTest[] = array($processExecutorMock, array('6f8ce5e29cdcd894d6b7aaeb590e656f9348ac3a' => 'master', '162eb4911ffc5e30496b5ed6c40c5182c1fd22d8' => '1.0'));

        return $provideTest;
    }

    /**
     * Returns the test data and expected results for testing GitAdapter::getTags.
     *
     * @return array
     */
    public function provideTestGetTags()
    {
        $tagsCommand = sprintf('git ls-remote --tags %s', ProcessUtils::escapeArgument('https://github.com/accompli/chrono.git'));

        $provideTest = array();

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo($tagsCommand))
                ->willReturn(new ProcessExecutionResult(1, '', ''));
        $provideTest[] = array($processExecutorMock, array());

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo($tagsCommand))
                ->willReturn(new ProcessExecutionResult(0, "bd82122129c6da32086988a89f8820e05128f1c9        refs/tags/0.1.0^{}\n3c6a664eea344ce2b9f1ce49922c4f12f57fca82        refs/tags/0.1.1^{}\n", ''));
        $provideTest[] = array($processExecutorMock, array('bd82122129c6da32086988a89f8820e05128f1c9' => '0.1.0', '3c6a664eea344ce2b9f1ce49922c4f12f57fca82' => '0.1.1'));

        return $provideTest;
    }

    /**
     * Returns the test data and expected results for testing GitAdapter::checkout.
     *
     * @return array
     */
    public function provideTestCheckout()
    {
        $cloneCommand = sprintf('git clone -b %s --single-branch %s %s', ProcessUtils::escapeArgument('0.1.0'), ProcessUtils::escapeArgument('https://github.com/accompli/chrono.git'), ProcessUtils::escapeArgument('/git/working-directory'));
        $checkoutCommand = sprintf('git checkout %s', ProcessUtils::escapeArgument('0.1.0'));

        $provideTest = array();

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(false);
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo($cloneCommand))
                ->willReturn(new ProcessExecutionResult(1, '', ''));
        $provideTest[] = array($processExecutorMock, false);

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(true);
        $processExecutorMock->expects($this->exactly(3))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo('git rev-parse --is-inside-work-tree'), $this->equalTo('/git/working-directory')),
                        array($this->equalTo('git fetch')),
                        array($this->equalTo($checkoutCommand), $this->equalTo('/git/working-directory'))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(1, '', '')
                );
        $provideTest[] = array($processExecutorMock, false);

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(false);
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo($cloneCommand))
                ->willReturn(new ProcessExecutionResult(0, '', ''));
        $provideTest[] = array($processExecutorMock, true);

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(true);
        $processExecutorMock->expects($this->exactly(4))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo('git rev-parse --is-inside-work-tree'), $this->equalTo('/git/working-directory')),
                        array($this->equalTo('git fetch')),
                        array($this->equalTo($checkoutCommand), $this->equalTo('/git/working-directory')),
                        array($this->equalTo('git pull'), $this->equalTo('/git/working-directory'))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(0, '', '')
                );
        $provideTest[] = array($processExecutorMock, true);

        return $provideTest;
    }
}
