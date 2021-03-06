<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Amp\Success;
use Exception;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Task\Exception\TaskError;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

class YamlHandler implements Handler
{
    public function __construct()
    {
    }

    public function taskFqn(): string
    {
        return YamlTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof YamlTask);
        $this->runYaml($context->service(Filesystem::class), $task);

        return new Success($context);
    }

    private function runYaml(Filesystem $filesystem, YamlTask $task): void
    {
        $existingData = [];

        if ($filesystem->exists($task->path())) {
            try {
                /** @var array $existingData */
                $existingData = Yaml::parse($filesystem->getContents($task->path()));
                Assert::isArray($existingData, 'YAML contents must be an array');
            } catch (Exception $e) {
                throw new TaskError(sprintf(
                    'Could not parse YAML: "%s"',
                    $e->getMessage()
                ), 0, $e);
            }
        }

        $data = array_merge($existingData, $task->data());

        if ($filter = $task->filter()) {
            $data = $filter($data);
        }

        $filesystem->putContents(
            $task->path(),
            Yaml::dump($data, $task->inline())
        );
    }
}
