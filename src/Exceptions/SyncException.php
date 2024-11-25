<?php

namespace VanOns\LaravelTranslationsSync\Exceptions;

use Exception;

class SyncException extends Exception
{
    public static function providerNotFound(string $provider): static
    {
        return new static("No valid sync provider found with key \"$provider\"");
    }

    public static function providerNotConfigured(): static
    {
        return new static('The sync provider is not configured properly');
    }

    public static function setupFailed(string $reason): static
    {
        return new static("Setup failed: $reason");
    }
}
