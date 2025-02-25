<?php

namespace VanOns\LaravelTranslationsSync\Services\Traits;

use Illuminate\Console\Command;

trait HasCommand
{
    protected Command $command;

    public function setCommand(Command $command): void
    {
        $this->command = $command;
    }

    protected function line(string $message): void
    {
        $this->command->line(sprintf('[%s] %s', $this->getName(), $message));
    }

    protected function info(string $message): void
    {
        $this->command->info(sprintf('[%s] %s', $this->getName(), $message));
    }

    protected function warn(string $message): void
    {
        $this->command->warn(sprintf('[%s] %s', $this->getName(), $message));
    }
}
