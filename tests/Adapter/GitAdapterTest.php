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
     * The URL of the repository used for testing.
     *
     * @var string
     */
    private $repositoryUrl = 'https://github.com/accompli/chrono.git';

    /**
     * The expected clone command.
     *
     * @var string
     */
    private $cloneCommand = "git clone -b '0.1.0' --single-branch 'https://github.com/accompli/chrono.git' '/git/working-directory'";

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
        $gitAdapter = new GitAdapter($this->repositoryUrl, '', $processExecutor);

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
        $gitAdapter = new GitAdapter($this->repositoryUrl, '', $processExecutor);

        $this->assertSame($expectedResult, $gitAdapter->getTags());
    }

    /**
     * Tests if GitAdapter::checkout clones when the specified working directory does not exist.
     */
    public function testCheckoutWithoutExistingCloneSuccesful()
    {
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->with($this->equalTo('/git/working-directory'))
                ->willReturn(false);
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo($this->cloneCommand), $this->equalTo(null), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo')))
                ->willReturn(new ProcessExecutionResult(0, '', ''));

        $gitAdapter = new GitAdapter($this->repositoryUrl, '/git/working-directory', $processExecutorMock);

        $this->assertTrue($gitAdapter->checkout('0.1.0'));
    }

    /**
     * Tests if GitAdapter::checkout clones when the specified working directory does not exist and returns false when it fails.
     */
    public function testCheckoutWithoutExistingCloneFailure()
    {
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->with($this->equalTo('/git/working-directory'))
                ->willReturn(false);
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo($this->cloneCommand), $this->equalTo(null), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo')))
                ->willReturn(new ProcessExecutionResult(1, '', ''));

        $gitAdapter = new GitAdapter($this->repositoryUrl, '/git/working-directory', $processExecutorMock);

        $this->assertFalse($gitAdapter->checkout('0.1.0'));
    }

    /**
     * Tests if GitAdapter::checkout calls the commands to checkout a tag.
     */
    public function testCheckoutTagWithExistingClone()
    {
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->with($this->equalTo('/git/working-directory'))
                ->willReturn(true);
        $processExecutorMock->expects($this->exactly(4))
                ->method('execute')
                ->withConsecutive(
                    array($this->equalTo('git rev-parse --is-inside-work-tree'), $this->equalTo('/git/working-directory'), $this->equalTo(null)),
                    array($this->equalTo('git fetch'), $this->equalTo('/git/working-directory'), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo'))),
                    array($this->equalTo("git checkout '0.1.0'"), $this->equalTo('/git/working-directory'), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo'))),
                    array($this->equalTo('git branch'), $this->equalTo('/git/working-directory'), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo')))
                )
                ->willReturnOnConsecutiveCalls(
                    new ProcessExecutionResult(0, '', ''),
                    new ProcessExecutionResult(0, '', ''),
                    new ProcessExecutionResult(0, '', ''),
                    new ProcessExecutionResult(0, " * (no branch)\n", '')
                );

        $gitAdapter = new GitAdapter($this->repositoryUrl, '/git/working-directory', $processExecutorMock);

        $this->assertTrue($gitAdapter->checkout('0.1.0'));
    }

    /**
     * Tests if GitAdapter::checkout calls the commands to checkout a tag and returns false when it fails.
     */
    public function testCheckoutTagWithExistingCloneFailure()
    {
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->with($this->equalTo('/git/working-directory'))
                ->willReturn(true);
        $processExecutorMock->expects($this->exactly(3))
                ->method('execute')
                ->withConsecutive(
                    array($this->equalTo('git rev-parse --is-inside-work-tree'), $this->equalTo('/git/working-directory'), $this->equalTo(null)),
                    array($this->equalTo('git fetch'), $this->equalTo('/git/working-directory'), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo'))),
                    array($this->equalTo('git branch'), $this->equalTo('/git/working-directory'), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo')))
                )
                ->willReturnOnConsecutiveCalls(
                    new ProcessExecutionResult(0, '', ''),
                    new ProcessExecutionResult(1, '', ''),
                    new ProcessExecutionResult(0, " * (no branch)\n", '')
                );

        $gitAdapter = new GitAdapter($this->repositoryUrl, '/git/working-directory', $processExecutorMock);

        $this->assertFalse($gitAdapter->checkout('0.1.0'));
    }

    /**
     * Tests if GitAdapter::checkout calls the commands to checkout a branch.
     */
    public function testCheckoutBranchWithExistingClone()
    {
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->with($this->equalTo('/git/working-directory'))
                ->willReturn(true);
        $processExecutorMock->expects($this->exactly(5))
                ->method('execute')
                ->withConsecutive(
                    array($this->equalTo('git rev-parse --is-inside-work-tree'), $this->equalTo('/git/working-directory'), $this->equalTo(null)),
                    array($this->equalTo('git fetch'), $this->equalTo('/git/working-directory'), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo'))),
                    array($this->equalTo("git checkout 'master'"), $this->equalTo('/git/working-directory'), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo'))),
                    array($this->equalTo('git branch'), $this->equalTo('/git/working-directory'), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo'))),
                    array($this->equalTo('git pull'), $this->equalTo('/git/working-directory'), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo')))
                )
                ->willReturnOnConsecutiveCalls(
                    new ProcessExecutionResult(0, '', ''),
                    new ProcessExecutionResult(0, '', ''),
                    new ProcessExecutionResult(0, '', ''),
                    new ProcessExecutionResult(0, " * master\n", ''),
                    new ProcessExecutionResult(0, '', '')
                );

        $gitAdapter = new GitAdapter($this->repositoryUrl, '/git/working-directory', $processExecutorMock);

        $this->assertTrue($gitAdapter->checkout('master'));
    }

    /**
     * Tests if GitAdapter::checkout calls the commands to checkout a branch and returns false when it fails.
     */
    public function testCheckoutBranchWithExistingCloneFailure()
    {
        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->with($this->equalTo('/git/working-directory'))
                ->willReturn(true);
        $processExecutorMock->expects($this->exactly(4))
                ->method('execute')
                ->withConsecutive(
                    array($this->equalTo('git rev-parse --is-inside-work-tree'), $this->equalTo('/git/working-directory'), $this->equalTo(null)),
                    array($this->equalTo('git fetch'), $this->equalTo('/git/working-directory'), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo'))),
                    array($this->equalTo("git checkout 'master'"), $this->equalTo('/git/working-directory'), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo'))),
                    array($this->equalTo('git branch'), $this->equalTo('/git/working-directory'), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo')))
                )
                ->willReturnOnConsecutiveCalls(
                    new ProcessExecutionResult(0, '', ''),
                    new ProcessExecutionResult(0, '', ''),
                    new ProcessExecutionResult(0, '', ''),
                    new ProcessExecutionResult(1, '', '')
                );

        $gitAdapter = new GitAdapter($this->repositoryUrl, '/git/working-directory', $processExecutorMock);

        $this->assertFalse($gitAdapter->checkout('master'));
    }

    /**
     * Returns the test data and expected results for testing GitAdapter::supportsRepository.
     *
     * @return array
     */
    public function provideTestSupportsRepository()
    {
        $provideTest = array();

        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo('git --version'))
                ->willReturn(new ProcessExecutionResult(1, '', ''));
        $provideTest[] = array('https://github.com/accompli/chrono.git', $processExecutorMock, false);

        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo('git --version'))
                ->willReturn(new ProcessExecutionResult(0, '', ''));
        $provideTest[] = array('https://github.com/accompli/chrono.git', $processExecutorMock, true);

        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo('git --version'))
                ->willReturn(new ProcessExecutionResult(0, '', ''));
        $provideTest[] = array('git@github.com:accompli/chrono.git', $processExecutorMock, true);

        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
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

        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->exactly(2))
                ->method('execute')
                ->withConsecutive(
                    array($this->equalTo('git --version')),
                    array($this->equalTo(sprintf('git ls-remote --heads %s', ProcessUtils::escapeArgument('svn+ssh://example.com/accompli/chrono'))), $this->equalTo(null), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo')))
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

        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo($branchesCommand), $this->equalTo(null), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo')))
                ->willReturn(new ProcessExecutionResult(1, '', ''));
        $provideTest[] = array($processExecutorMock, array());

        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo($branchesCommand), $this->equalTo(null), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo')))
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

        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo($tagsCommand), $this->equalTo(null), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo')))
                ->willReturn(new ProcessExecutionResult(1, '', ''));
        $provideTest[] = array($processExecutorMock, array());

        $processExecutorMock = $this->getMockBuilder(ProcessExecutorInterface::class)
                ->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo($tagsCommand), $this->equalTo(null), $this->equalTo(array('GIT_TERMINAL_PROMPT' => '0', 'GIT_ASKPASS' => 'echo')))
                ->willReturn(new ProcessExecutionResult(0, "bd82122129c6da32086988a89f8820e05128f1c9        refs/tags/0.1.0^{}\n3c6a664eea344ce2b9f1ce49922c4f12f57fca82        refs/tags/0.1.1^{}\n", ''));
        $provideTest[] = array($processExecutorMock, array('bd82122129c6da32086988a89f8820e05128f1c9' => '0.1.0', '3c6a664eea344ce2b9f1ce49922c4f12f57fca82' => '0.1.1'));

        return $provideTest;
    }
}
