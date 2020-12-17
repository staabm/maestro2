<?php

namespace Maestro2\Core\Config;

use DTL\Invoke\Invoke;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Fact\PhpFact;

final class MainNode
{
    /**
     * @var array<RepositoryNode>
     */
    private array $repositories;
    private PhpFact $php;

    /**
     * @param array<array<string, mixed>> $repositories
     * @param array<string> $selectedRepositories
     */
    public function __construct(
        private string $workspacePath,
        array $repositories,
        private array $templatePaths = [],
        private array $vars = [],
        private ?array $selectedRepositories = null,
        array $php = []
    )
    {
        $this->php = Invoke::new(PhpFact::class, $php);
        $this->repositories = (function (array $repositories) {
            return array_combine(array_map(
                fn (RepositoryNode $r) => $r->name(),
                $repositories
            ), $repositories);
        })(array_map(
            fn (array $repository): RepositoryNode => Invoke::new(RepositoryNode::class, array_merge([
                'main' => $this,
            ], $repository)),
            $repositories
        ));
    }

    /**
     * @param array<string, mixed> $main
     */
    public static function fromArray(array $main): self
    {
        return Invoke::new(self::class, $main);
    }

    /**
     * @return array<RepositoryNode>
     */
    public function selectedRepositories(): array
    {
        if (null === $this->selectedRepositories) {
            return $this->repositories;
        }
        return array_map(function (string $name) {
            if (!isset($this->repositories[$name])) {
                throw new RuntimeException(sprintf(
                    'Repository "%s" not known, known repositories "%s"',
                    $name,
                    implode('", "', array_keys($this->repositories))
                ));
            }

            return $this->repositories[$name];
        }, $this->selectedRepositories);
    }

    /**
     * @return array<RepositoryNode>
     */
    public function repositories(): array
    {
        return $this->repositories;
    }

    public function workspacePath(): string
    {
        return $this->workspacePath;
    }

    public function vars(): Vars
    {
        return new Vars($this->vars);
    }

    public function withSelectedRepos(?array $repos): self
    {
        $new = clone $this;
        $new->selectedRepositories = $repos;

        return $new;
    }

    public function php(): PhpFact
    {
        return $this->php;
    }

    public function templatePaths(): array
    {
        return $this->templatePaths;
    }
}
