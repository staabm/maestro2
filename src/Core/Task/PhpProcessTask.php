<?php

namespace Maestro\Core\Task;

use Closure;
use Maestro\Core\Process\ProcessResult;

class PhpProcessTask implements Task
{
    /**
     * @param list<string>|string $cmd
     * @param (Closure(ProcessResult, Context): Context)|null $after
     */
    public function __construct(
        private array|string $cmd,
        private ?Closure $after = null,
        private bool $allowFailure = false
    ) {
    }

    /**
     * @return (Closure(ProcessResult, Context): Context)|null
     */
    public function after(): ?Closure
    {
        return $this->after;
    }

    public function allowFailure(): bool
    {
        return $this->allowFailure;
    }

    /**
     * @return list<string>|string
     */
    public function cmd(): array|string
    {
        return $this->cmd;
    }
}
