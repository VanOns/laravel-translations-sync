<?php

namespace VanOns\LaravelTranslationsSync\Exceptions;

use Exception;

class SyncException extends Exception
{
    public static function providerNotFound(string $provider): self
    {
        return new self("No valid sync provider found with key \"{$provider}\"");
    }

    public static function providerNotConfigured(): self
    {
        return new self('The sync provider is not configured properly');
    }

    public static function setupFailed(string $reason): self
    {
        return new self("Setup failed: {$reason}");
    }
}
