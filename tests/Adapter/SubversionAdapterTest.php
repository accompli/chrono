<?php

namespace Accompli\Chrono\Test;

use Accompli\Chrono\Adapter\SubversionAdapter;
use Accompli\Chrono\Process\ProcessExecutionResult;
use Accompli\Chrono\Process\ProcessExecutorInterface;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Process\ProcessUtils;

/**
 * SubversionAdapterTest.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class SubversionAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if SubversionAdapter::supportsRepository returns the expected result.
     *
     * @dataProvider provideTestSupportsRepository
     *
     * @param string                   $repositoryUrl
     * @param ProcessExecutorInterface $processExecutor
     * @param bool                     $expectedResult
     */
    public function testSupportsRepository($repositoryUrl, ProcessExecutorInterface $processExecutor, $expectedResult)
    {
        $subversionAdapter = new SubversionAdapter($repositoryUrl, '', $processExecutor);

        $this->assertSame($expectedResult, $subversionAdapter->supportsRepository());
    }

    /**
     * Tests if SubversionAdapter::getBranches returns the expected result.
     *
     * @dataProvider provideTestGetBranches
     *
     * @param ProcessExecutorInterface $processExecutor
     * @param array                    $expectedResult
     */
    public function testGetBranches(ProcessExecutorInterface $processExecutor, array $expectedResult)
    {
        $subversionAdapter = new SubversionAdapter('https://github.com/accompli/chrono', '', $processExecutor);

        $this->assertSame($expectedResult, $subversionAdapter->getBranches());
    }

    /**
     * Tests if SubversionAdapter::getTags returns the expected result.
     *
     * @dataProvider provideTestGetTags
     *
     * @param ProcessExecutorInterface $processExecutor
     * @param array                    $expectedResult
     */
    public function testGetTags(ProcessExecutorInterface $processExecutor, array $expectedResult)
    {
        $subversionAdapter = new SubversionAdapter('https://github.com/accompli/chrono', '', $processExecutor);

        $this->assertSame($expectedResult, $subversionAdapter->getTags());
    }

    /**
     * Tests if SubversionAdapter::checkout returns the expected result.
     *
     * @dataProvider provideTestCheckout
     *
     * @param ProcessExecutorInterface $processExecutor
     * @param bool                     $expectedResult
     * @param string                   $version
     */
    public function testCheckout(ProcessExecutorInterface $processExecutor, $expectedResult, $version = '0.1.0')
    {
        $subversionAdapter = new SubversionAdapter('https://github.com/accompli/chrono', '/svn/working-directory', $processExecutor);

        $this->assertSame($expectedResult, $subversionAdapter->checkout($version));
    }

    /**
     * Returns the test data and expected results for testing SubversionAdapter::supportsRepository.
     *
     * @return array
     */
    public function provideTestSupportsRepository()
    {
        $infoCommand = 'svn info --non-interactive %s';

        $provideTest = array();

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo('svn --version'))
                ->willReturn(new ProcessExecutionResult(1, '', ''));
        $provideTest[] = array('https://github.com/accompli/chrono.git', $processExecutorMock, false);

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->exactly(2))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo('svn --version')),
                        array($this->equalTo(sprintf($infoCommand, ProcessUtils::escapeArgument('https://github.com/accompli/chrono.git'))))
                )
                ->willReturn(new ProcessExecutionResult(0, '', ''));
        $provideTest[] = array('https://github.com/accompli/chrono.git', $processExecutorMock, true);

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->exactly(2))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo('svn --version')),
                        array($this->equalTo(sprintf($infoCommand, ProcessUtils::escapeArgument('git@github.com:accompli/chrono.git'))))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(1, '', '')
                );
        $provideTest[] = array('git@github.com:accompli/chrono.git', $processExecutorMock, false);

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->exactly(2))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo('svn --version')),
                        array($this->equalTo(sprintf($infoCommand, ProcessUtils::escapeArgument('user@example.com:accompli/chrono'))))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(1, '', '')
                );
        $provideTest[] = array('user@example.com:accompli/chrono', $processExecutorMock, false);

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('execute')
                ->with($this->equalTo('svn --version'))
                ->willReturn(new ProcessExecutionResult(0, '', ''));
        $provideTest[] = array('svn+ssh://example.com/accompli/chrono', $processExecutorMock, true);

        return $provideTest;
    }

    /**
     * Returns the test data and expected results for testing SubversionAdapter::getBranches.
     *
     * @return array
     */
    public function provideTestGetBranches()
    {
        $trunkCommand = sprintf('svn ls --non-interactive %s', ProcessUtils::escapeArgument('https://github.com/accompli/chrono/trunk'));
        $branchesCommand = sprintf('svn ls --non-interactive %s', ProcessUtils::escapeArgument('https://github.com/accompli/chrono/branches'));

        $provideTest = array();

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->exactly(2))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo($trunkCommand)),
                        array($this->equalTo($branchesCommand))
                )
                ->willReturn(new ProcessExecutionResult(1, '', ''));
        $provideTest[] = array($processExecutorMock, array());

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->exactly(2))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo($trunkCommand)),
                        array($this->equalTo($branchesCommand))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(1, '', ''),
                        new ProcessExecutionResult(0, "     35 niels.ni              nov 22 22:10 ./\n     35 niels.ni              nov 22 22:10 1.0\n", '')
                );
        $provideTest[] = array($processExecutorMock, array('35' => '1.0'));

        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->exactly(2))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo($trunkCommand)),
                        array($this->equalTo($branchesCommand))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, "     34 niels.ni              nov 22 22:10 ./\n     34 niels.ni              nov 22 22:10 file\n", ''),
                        new ProcessExecutionResult(0, "     35 niels.ni              nov 22 22:10 ./\n     35 niels.ni              nov 22 22:10 1.0\n", '')
                );
        $provideTest[] = array($processExecutorMock, array('34' => 'master', '35' => '1.0'));

        return $provideTest;
    }

    /**
     * Returns the test data and expected results for testing SubversionAdapter::getTags.
     *
     * @return array
     */
    public function provideTestGetTags()
    {
        $tagsCommand = sprintf('svn ls --non-interactive %s', ProcessUtils::escapeArgument('https://github.com/accompli/chrono/tags'));

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
                ->willReturn(new ProcessExecutionResult(0, "     35 niels.ni              nov 22 22:10 ./\n     35 niels.ni              nov 22 22:10 0.1.0\n", ''));
        $provideTest[] = array($processExecutorMock, array('35' => '0.1.0'));

        return $provideTest;
    }

    /**
     * Returns the test data and expected results for testing SubversionAdapter::checkout.
     *
     * @return array
     */
    public function provideTestCheckout()
    {
        $getTrunkCommand = sprintf('svn ls --non-interactive %s', ProcessUtils::escapeArgument('https://github.com/accompli/chrono/trunk'));
        $getBranchesCommand = sprintf('svn ls --non-interactive %s', ProcessUtils::escapeArgument('https://github.com/accompli/chrono/branches'));
        $getTagsCommand = sprintf('svn ls --non-interactive %s', ProcessUtils::escapeArgument('https://github.com/accompli/chrono/tags'));
        $infoCommand = 'svn info --non-interactive';
        $trunkSwitchCommand = sprintf('svn switch --non-interactive %s', ProcessUtils::escapeArgument('https://github.com/accompli/chrono/trunk'));
        $branchSwitchCommand = sprintf('svn switch --non-interactive %s', ProcessUtils::escapeArgument('https://github.com/accompli/chrono/branches/1.0'));
        $tagSwitchCommand = sprintf('svn switch --non-interactive %s', ProcessUtils::escapeArgument('https://github.com/accompli/chrono/tags/0.1.0'));
        $checkoutCommand = sprintf('svn checkout --non-interactive %s %s', ProcessUtils::escapeArgument('https://github.com/accompli/chrono/tags/0.1.0'), ProcessUtils::escapeArgument('/svn/working-directory'));

        $provideTest = array();

        // directory false, no branches, no tags
        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(false);
        $processExecutorMock->expects($this->exactly(3))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo($getTrunkCommand)),
                        array($this->equalTo($getBranchesCommand)),
                        array($this->equalTo($getTagsCommand))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(1, '', ''),
                        new ProcessExecutionResult(1, '', ''),
                        new ProcessExecutionResult(1, '', '')
                );
        $provideTest[] = array($processExecutorMock, false);

        // directory false, branches, tags, checkout failed
        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(false);
        $processExecutorMock->expects($this->exactly(4))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo($getTrunkCommand)),
                        array($this->equalTo($getBranchesCommand)),
                        array($this->equalTo($getTagsCommand)),
                        array($this->equalTo($checkoutCommand))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, "     34 niels.ni              nov 22 22:10 ./\n     34 niels.ni              nov 22 22:10 file\n", ''),
                        new ProcessExecutionResult(0, "     35 niels.ni              nov 22 22:10 ./\n     35 niels.ni              nov 22 22:10 1.0\n", ''),
                        new ProcessExecutionResult(0, "     35 niels.ni              nov 22 22:10 ./\n     35 niels.ni              nov 22 22:10 0.1.0\n", ''),
                        new ProcessExecutionResult(1, '', '')
                );
        $provideTest[] = array($processExecutorMock, false);

        // directory false, branches, tags, checkout success
        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(false);
        $processExecutorMock->expects($this->exactly(4))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo($getTrunkCommand)),
                        array($this->equalTo($getBranchesCommand)),
                        array($this->equalTo($getTagsCommand)),
                        array($this->equalTo($checkoutCommand))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, "     34 niels.ni              nov 22 22:10 ./\n     34 niels.ni              nov 22 22:10 file\n", ''),
                        new ProcessExecutionResult(0, "     35 niels.ni              nov 22 22:10 ./\n     35 niels.ni              nov 22 22:10 1.0\n", ''),
                        new ProcessExecutionResult(0, "     35 niels.ni              nov 22 22:10 ./\n     35 niels.ni              nov 22 22:10 0.1.0\n", ''),
                        new ProcessExecutionResult(0, '', '')
                );
        $provideTest[] = array($processExecutorMock, true);

        // directory true, no branches, no tags
        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(true);
        $processExecutorMock->expects($this->exactly(3))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo($getTrunkCommand)),
                        array($this->equalTo($getBranchesCommand)),
                        array($this->equalTo($getTagsCommand))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(1, '', ''),
                        new ProcessExecutionResult(1, '', ''),
                        new ProcessExecutionResult(1, '', '')
                );
        $provideTest[] = array($processExecutorMock, false);

        // directory true, branches, tags, switch failed
        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(true);
        $processExecutorMock->expects($this->exactly(5))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo($getTrunkCommand)),
                        array($this->equalTo($getBranchesCommand)),
                        array($this->equalTo($getTagsCommand)),
                        array($this->equalTo($infoCommand)),
                        array($this->equalTo($tagSwitchCommand))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, "     34 niels.ni              nov 22 22:10 ./\n     34 niels.ni              nov 22 22:10 file\n", ''),
                        new ProcessExecutionResult(0, "     35 niels.ni              nov 22 22:10 ./\n     35 niels.ni              nov 22 22:10 1.0\n", ''),
                        new ProcessExecutionResult(0, "     35 niels.ni              nov 22 22:10 ./\n     35 niels.ni              nov 22 22:10 0.1.0\n", ''),
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(1, '', '')
                );
        $provideTest[] = array($processExecutorMock, false);

        // directory true, branches, tags, switch success
        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(true);
        $processExecutorMock->expects($this->exactly(5))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo($getTrunkCommand)),
                        array($this->equalTo($getBranchesCommand)),
                        array($this->equalTo($getTagsCommand)),
                        array($this->equalTo($infoCommand)),
                        array($this->equalTo($tagSwitchCommand))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, "     34 niels.ni              nov 22 22:10 ./\n     34 niels.ni              nov 22 22:10 file\n", ''),
                        new ProcessExecutionResult(0, "     35 niels.ni              nov 22 22:10 ./\n     35 niels.ni              nov 22 22:10 1.0\n", ''),
                        new ProcessExecutionResult(0, "     35 niels.ni              nov 22 22:10 ./\n     35 niels.ni              nov 22 22:10 0.1.0\n", ''),
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(0, '', '')
                );
        $provideTest[] = array($processExecutorMock, true);

        // directory true, branches, tags, switch to 'master'
        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(true);
        $processExecutorMock->expects($this->exactly(3))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo($infoCommand)),
                        array($this->equalTo($trunkSwitchCommand))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(0, '', '')
                );
        $provideTest[] = array($processExecutorMock, true, 'master');

        // directory true, branches, switch to '1.0'
        $processExecutorMock = $this->getMockBuilder('Accompli\Chrono\Process\ProcessExecutorInterface')->getMock();
        $processExecutorMock->expects($this->once())
                ->method('isDirectory')
                ->willReturn(true);
        $processExecutorMock->expects($this->exactly(4))
                ->method('execute')
                ->withConsecutive(
                        array($this->equalTo($getTrunkCommand)),
                        array($this->equalTo($getBranchesCommand)),
                        array($this->equalTo($infoCommand)),
                        array($this->equalTo($branchSwitchCommand))
                )
                ->willReturnOnConsecutiveCalls(
                        new ProcessExecutionResult(0, "     34 niels.ni              nov 22 22:10 ./\n     34 niels.ni              nov 22 22:10 file\n", ''),
                        new ProcessExecutionResult(0, "     35 niels.ni              nov 22 22:10 ./\n     35 niels.ni              nov 22 22:10 1.0\n", ''),
                        new ProcessExecutionResult(0, '', ''),
                        new ProcessExecutionResult(0, '', '')
                );
        $provideTest[] = array($processExecutorMock, true, '1.0');

        return $provideTest;
    }
}
