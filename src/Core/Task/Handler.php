<?php

namespace Maestro\Core\Task;

use Amp\Promise;

interface Handler
{
    public function taskFqn(): string;

    /**
     * @return Promise<Context>
     */
    public function run(Task $task, Context $context): Promise;
}
