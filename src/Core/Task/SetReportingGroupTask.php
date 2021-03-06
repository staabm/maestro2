<?php

namespace Maestro\Core\Task;

use Amp\Success;
use Maestro\Core\Report\TaskReportPublisher;

class SetReportingGroupTask implements DelegateTask
{
    public function __construct(private string $group)
    {
    }

    public function group(): string
    {
        return $this->group;
    }

    public function task(): Task
    {
        return new ClosureTask(
            closure: function (array $args, Context $context) {
                return new Success($context->withService(
                    $context->service(TaskReportPublisher::class)->withGroup($this->group())
                ));
            }
        );
    }
}
