<?php

namespace Maestro2\Core\Extension\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger extends AbstractLogger
{
    private float $start;

    public function __construct(private OutputInterface $output)
    {
        $this->start = microtime(true);
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = [])
    {
        $this->output->writeln(sprintf(
            '[%s] [<%s>%s</>] %s',
            number_format(microtime(true) - $this->start, 4),
            match ($level) {
                LogLevel::INFO => 'fg=green',
                LogLevel::DEBUG => 'fg=cyan',
                LogLevel::ERROR => 'fg=red',
                default => 'fg=white',
            },
            substr($level, 0, 4),
            $message
        ));
}
}
