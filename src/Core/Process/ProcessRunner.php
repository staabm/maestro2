<?php

namespace Maestro\Core\Process;

use Amp\Promise;

interface ProcessRunner
{
    /**
     * @param array<array-key,string> $args
     * @return Promise<ProcessResult>
     */
    public function run(array $args, ?string $cwd = null): Promise;

    /**
     * @param array<array-key,string> $args
     * @return Promise<ProcessResult>
     */
    public function mustRun(array $args, ?string $cwd = null): Promise;
}
