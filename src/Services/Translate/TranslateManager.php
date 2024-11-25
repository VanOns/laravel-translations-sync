<?php

namespace VanOns\LaravelTranslationsSync\Services\Translate;

use Illuminate\Console\Command;
use VanOns\LaravelTranslationsSync\Exceptions\TranslateException;

class TranslateManager
{
    protected ?BaseTranslateService $service = null;

    /**
     * @throws TranslateException
     */
    public function __construct()
    {
        $provider = config('translations-sync.translate_provider');

        $this->service = match ($provider) {
            'deepl' => app(DeeplService::class),
            default => throw TranslateException::providerNotFound($provider),
        };
    }

    public function withCommand(Command $command): static
    {
        $this->service->setCommand($command);

        return $this;
    }

    public function getService(): ?BaseTranslateService
    {
        return $this->service;
    }
}
