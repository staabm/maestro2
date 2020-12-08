<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Task\Exception\TaskError;
use function Amp\call;

class GitCommitHandler implements Handler
{
    private ProcessRunner $runner;

    public function __construct(ProcessRunner $runner)
    {
        $this->runner = $runner;
    }

    public function taskFqn(): string
    {
        return GitCommitTask::class;
    }

    public function run(Task $task): Promise
    {
        assert($task instanceof GitCommitTask);
        return call(function () use ($task) {
            $result = yield $this->runner->run([
                'git',
                'rev-parse',
                '--show-toplevel',
            ], $task->cwd());
            assert($result instanceof ProcessResult);

            if (!$result->isOk()) {
                throw new TaskError(sprintf(
                    'Path "%s" is not a git repository',
                    $task->cwd()
                ));
            }

            (function (string $topLevelPath) use ($task) {
                if ($topLevelPath === $task->cwd()) {
                    return;
                }
                throw new TaskError(sprintf(
                    'Path "%s" is not the root of a git repository (root is "%s")',
                    $task->cwd(),
                    $topLevelPath
                ));
            })(trim($result->stdOut()));

            yield $this->runner->mustRun(
                array_merge([
                    'git', 'add'
                ], $task->paths()),
                $task->cwd(),
            );
            yield $this->runner->mustRun([
                'git', 'commit', '-m', $task->message()
            ], $task->cwd());
        });
    }
}