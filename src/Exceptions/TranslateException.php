<?php

namespace VanOns\LaravelTranslationsSync\Exceptions;

use Exception;

class TranslateException extends Exception
{
    public static function providerNotFound(string $provider): static
    {
        return new static("No valid translate provider found with key \"$provider\"");
    }

    public static function providerNotConfigured(): static
    {
        return new static('The translate provider is not configured properly');
    }

    public static function translateFailed(?string $error = null): static
    {
        $message = 'Failed to translate text';
        if ($error) {
            $message .= ': ' . $error;
        }

        return new static($message);
    }
}
