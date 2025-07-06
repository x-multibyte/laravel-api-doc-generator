<?php

namespace XMultibyte\ApiDoc\Console\Commands;

use Illuminate\Console\Command;
use XMultibyte\ApiDoc\ApiDocsGenerator;

abstract class BaseCommand extends Command
{
    protected $generator;

    public function __construct(ApiDocsGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    protected function displayHeader($title)
    {
        $this->line('');
        $this->line('<fg=cyan>╔══════════════════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan>║</> <fg=white;options=bold>' . str_pad($title, 58, ' ', STR_PAD_BOTH) . '</> <fg=cyan>║</>');
        $this->line('<fg=cyan>╚══════════════════════════════════════════════════════════════╝</>');
        $this->line('');
    }

    protected function displaySuccess($message)
    {
        $this->line('<fg=green>✓</> ' . $message);
    }

    protected function displayError($message)
    {
        $this->line('<fg=red>✗</> ' . $message);
    }

    protected function displayWarning($message)
    {
        $this->line('<fg=yellow>⚠</> ' . $message);
    }

    protected function displayInfo($message)
    {
        $this->line('<fg=blue>ℹ</> ' . $message);
    }

    protected function createProgressBar($max)
    {
        $progressBar = $this->output->createProgressBar($max);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        return $progressBar;
    }
}
