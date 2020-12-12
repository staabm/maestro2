<?php

namespace Maestro2\Core\Pipeline;

use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Task\Task;

class NullPipeline implements Pipeline
{
    public function build(MainNode $mainNode): Task
    {
    }
}