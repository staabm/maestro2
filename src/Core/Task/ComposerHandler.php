<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\JsonMergeTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use PhpCsFixer\Cache\FileHandler;
use Webmozart\PathUtil\Path;
use stdClass;
use function Amp\call;

class ComposerHandler implements Handler
{
    public function __construct(private Enqueuer $enqueuer, private ProcessRunner $runner)
    {
    }

    public function taskFqn(): string
    {
        return ComposerTask::class;
    }

    public function run(Task $task): Promise
    {
        assert($task instanceof ComposerTask);
        return call(function (string $requireType) use ($task) {
            yield $this->enqueuer->enqueue(
                $this->createJsonTask($task, $requireType)
            );

            if ($task->update() === true) {
                $this->runner->mustRun(['composer', 'update', '--working-dir=' . $task->path()]);
            }
        }, $task->dev() ? 'require-dev' : 'require');
    }

    private function createJsonTask(ComposerTask $task, string $requireType): JsonMergeTask
    {
        return new JsonMergeTask(
            path: Path::join([$task->path(), 'composer.json']),
            data: [
                $requireType => $task->require()
            ],
            filter: static function (stdClass $object) use ($task, $requireType) {
                foreach ($object->$requireType as $package => $version) {
                    if (in_array($package, $task->remove())) {
                        unset($object->$requireType->$package);
                    }
                }
                return $object;
            }
        );
    }
}