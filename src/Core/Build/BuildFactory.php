<?php

namespace Maestro2\Core\Build;

use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Queue\Worker;
use Maestro2\Core\Task\CommandsTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitRepositoryTask;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\ProcessTask;
use Maestro2\Core\Task\SequentialTask;

class BuildFactory
{
    public function __construct(private Enqueuer $queue, private Worker $worker)
    {
    }

    public function createBuild(MainNode $config): Build
    {
        $tasks = [
            new FileTask(
                type: 'directory',
                path: $config->workspacePath(),
                exists: false,
            ),
            new FileTask(
                type: 'directory',
                path: $config->workspacePath(),
                mode: 0777,
                exists: true,
            ),
        ];

        foreach ($config->repositories() as $repository) {
            $cwd = sprintf('%s/%s', $config->workspacePath(), $repository->name());
            $tasks[] = new SequentialTask([
                new GitRepositoryTask(
                    url: $repository->url(),
                    path: $cwd,
                ),
                new CommandsTask(
                    commands: [
                        [ 'php7.4', '/usr/local/bin/composer', 'install' ],
                        [ 'php7.4', './vendor/bin/phpunit' ],
                        [ 'php7.4', './vendor/bin/phpstan analyse' ],
                    ],
                    cwd: $cwd
                ),
            ]);
        }

        return new Build($this->queue, $tasks, $this->worker);
    }
}
