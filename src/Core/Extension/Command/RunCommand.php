<?php

namespace Maestro2\Core\Extension\Command;

use Amp\Loop;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportProvider;
use Maestro2\Maestro;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class RunCommand extends Command
{
    const NAME = 'run';
    const ARG_TARGET = 'target';


    public function __construct(
        private Maestro $maestro,
        private ReportProvider $reportProvider
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME);
        $this->addArgument(self::ARG_TARGET, InputArgument::IS_ARRAY, 'Targets to run');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Loop::setErrorHandler(function (Throwable $error) use ($output) {
            $output->writeln(sprintf('<error>%s</>', $error->getMessage()));
            throw $error;
        });
        Loop::run(function () use ($input) {
            yield $this->maestro->run($input->getArgument(self::ARG_TARGET));
        });

        $style = new SymfonyStyle($input, $output);

        $output->writeln('');
        foreach ($this->reportProvider->groups() as $group) {
            $style->section($group->name());
            foreach ($group->reports() as $report) {
                $output->writeln(sprintf(
                    "  %s %s",
                    match ($report->level()) {
                        Report::LEVEL_OK => '<fg=green>✔</>',
                        Report::LEVEL_FAIL => '<fg=red>✘</>',
                    },
                        $report->title()
                ));
                if ($report->body()) {
                    $style->block(
                        trim($report->body()),
                        escape: false,
                        padding: true,
                        prefix: '    ',
                    );
                }
            }
            $output->writeln('');
        }

        return 0;
    }
}
