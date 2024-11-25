<?php

namespace VanOns\LaravelTranslationsSync\Services\Sync;

use Illuminate\Console\Command;
use VanOns\LaravelTranslationsSync\Exceptions\SyncException;

class SyncManager
{
    protected ?BaseSyncService $service = null;

    /**
     * @throws SyncException
     */
    public function __construct()
    {
        $provider = config('translations-sync.sync_provider');

        $this->service = match ($provider) {
            'google_sheets' => app(GoogleSheetsService::class),
            default => throw SyncException::providerNotFound($provider),
        };
    }

    public function withCommand(Command $command): static
    {
        $this->service->setCommand($command);

        return $this;
    }

    public function getService(): ?BaseSyncService
    {
        return $this->service;
    }
}
