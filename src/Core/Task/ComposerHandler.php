<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\PhpFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Queue\Enqueuer;
use Symfony\Component\Process\ExecutableFinder;
use Webmozart\PathUtil\Path;
use stdClass;
use function Amp\call;

class ComposerHandler implements Handler
{
    public function __construct(
        private Filesystem $filesystem,
        private Enqueuer $enqueuer,
    ) {
    }

    public function taskFqn(): string
    {
        return ComposerTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ComposerTask);
        return call(
            function (string $requireType, Filesystem $filesystem) use ($task, $context) {
                yield $this->enqueuer->enqueue(
                    TaskContext::create(
                        $this->createJsonTask(
                            $task,
                            $requireType
                        ),
                        $context
                    )
                );


                if ($task->update() === true) {
                    $finder = new ExecutableFinder();
                    yield $this->enqueuer->enqueue(TaskContext::create(new PhpProcessTask(
                        args: [
                            $task->composerBin() ?: $finder->find('composer') ?: 'composer',
                            'update',
                        ]
                    ), $context));
                }

                return $context;
            },
            $task->dev() ? 'require-dev' : 'require',
            $this->filesystem->cd($context->factOrNull(CwdFact::class)?->cwd() ?: '/')
        );
    }

    private function createJsonTask(ComposerTask $task, string $requireType): JsonMergeTask
    {
        return new JsonMergeTask(
            path: 'composer.json',
            data: [
                $requireType => $task->require()
            ],
            filter: static function (stdClass $object) use ($task, $requireType) {
                foreach ($object->$requireType as $package => $version) {
                    if (in_array($package, $task->remove())) {
                        unset($object->$requireType->$package);
                    }
                }
                if (is_array($object->{$requireType})) {
                    $object->{$requireType} = (object)$object->{$requireType};
                }

                return $object;
            }
        );
    }
}
