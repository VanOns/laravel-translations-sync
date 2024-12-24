<?php

namespace VanOns\LaravelTranslationsSync\Exceptions;

use Exception;

class TranslateException extends Exception
{
    public static function providerNotFound(string $provider): self
    {
        return new self("No valid translate provider found with key \"{$provider}\"");
    }

    public static function providerNotConfigured(): self
    {
        return new self('The translate provider is not configured properly');
    }

    public static function translateFailed(?string $error = null): self
    {
        $message = 'Failed to translate text';
        if ($error) {
            $message .= ': ' . $error;
        }

        return new self($message);
    }
}
