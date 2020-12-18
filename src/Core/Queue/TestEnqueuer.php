<?php

namespace Maestro2\Core\Queue;

use Amp\Promise;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\TaskContext;
use Throwable;

final class TestEnqueuer implements Enqueuer, Dequeuer
{
    public function __construct(private HandlerFactory $handlerFactory)
    {
    }

    public static function fromHandlers(array $handlers): self
    {
        return new self(new HandlerFactory($handlers));
    }

    /**
     * {@inheritDoc}
     */
    public function enqueue(TaskContext $task): Promise
    {
        return $this->handlerFactory->handlerFor($task->task())->run($task->task(), $task->context());
    }

    public function dequeue(): ?TaskContext
    {
        return null;
    }

    public function resolve(TaskContext $task, ?Context $context, ?Throwable $error = null): void
    {
    }
}
