<?php

namespace Maestro2\Core\Report;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use Countable;

/**
 * @implements IteratorAggregate<Report>
 */
class Reports implements IteratorAggregate, Countable
{
    /**
     * @var array<array-key,Report>
     */
    private array $reports;

    public function __construct(array $reports)
    {
        $this->reports = $reports;
    }

    public function warns(): self
    {
        return new self(array_filter(
            $this->reports,
            fn (Report $report) => $report->level() === Report::LEVEL_WARN
        ));
    }

    public function fails(): self
    {
        return new self(array_filter(
            $this->reports,
            fn (Report $report) => $report->level() === Report::LEVEL_FAIL
        ));
    }

    public function infos(): self
    {
        return new self(array_filter(
            $this->reports,
            fn (Report $report) => $report->level() === Report::LEVEL_INFO
        ));
    }

    public function oks(): self
    {
        return new self(array_filter(
            $this->reports,
            fn (Report $report) => $report->level() === Report::LEVEL_OK
        ));
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->reports);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->reports);
    }
}
