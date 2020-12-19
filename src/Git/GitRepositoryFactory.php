<?php

namespace Maestro2\Git;

use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Vcs\Repository;
use Maestro2\Core\Vcs\RepositoryFactory;
use Psr\Log\LoggerInterface;

class GitRepositoryFactory implements RepositoryFactory
{
    public function __construct(private ProcessRunner $runner, private LoggerInterface $logger)
    {
    }

    public function create(string $cwd): Repository
    {
        return new GitRepository($this->runner, $this->logger, $cwd);
    }
}
