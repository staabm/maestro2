<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Process\TestProcessRunner;
use Maestro2\Core\Task\Exception\TaskError;
use Maestro2\Core\Task\GitCommitHandler;
use Maestro2\Core\Task\GitCommitTask;
use Maestro2\Core\Task\Handler;
use PHPUnit\Framework\TestCase;

class GitCommitHandlerTest extends HandlerTestCase
{
    private TestProcessRunner $testRunner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testRunner = new TestProcessRunner();
    }

    protected function createHandler(): Handler
    {
        return new GitCommitHandler(
            $this->testRunner
        );
    }

    public function testExecutedGitCommit(): void
    {
        $this->testRunner->push(ProcessResult::ok($this->workspace()->path()));
        $this->testRunner->push(ProcessResult::ok());
        $this->testRunner->push(ProcessResult::ok());

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
            cwd: $this->workspace()->path()
        ));

        self::assertEquals('git rev-parse --show-toplevel', $this->testRunner->pop()->argsAsString());
        self::assertEquals('git add foo bar', $this->testRunner->pop()->argsAsString());
        self::assertEquals('git commit -m Foobar', $this->testRunner->pop()->argsAsString());
    }

    public function testTaskErrorIfNotAGitRepository(): void
    {
        $this->expectException(TaskError::class);
        $this->expectExceptionMessage('is not a git');
        $this->testRunner->push(ProcessResult::new(128));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
            cwd: $this->workspace()->path()
        ));
    }

    public function testTaskErrorIfNotAGitRoot(): void
    {
        $this->expectException(TaskError::class);
        $this->expectExceptionMessage('is not the root');

        $this->testRunner->push(ProcessResult::ok('path/to/foo'));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
            cwd: $this->workspace()->path()
        ));
    }
}