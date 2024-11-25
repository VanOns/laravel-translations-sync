<?php

namespace VanOns\LaravelTranslationsSync\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use VanOns\LaravelTranslationsSync\Facades\LaravelTranslationsSync;
use VanOns\LaravelTranslationsSync\Services\Sync\BaseSyncService;
use VanOns\LaravelTranslationsSync\Services\Sync\SyncManager;
use VanOns\LaravelTranslationsSync\Services\Translate\BaseTranslateService;
use VanOns\LaravelTranslationsSync\Services\Translate\TranslateManager;

class SyncTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lang:sync
                            {--R|retrieve-only : Only write the translations to the files, don\'t update sync provider}
                            {--T|translate     : Translate missing translations using translate provider}
                            {--F|force         : Skip the confirmation dialog}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all translations with the configured provider';

    protected BaseSyncService $sync;

    protected ?BaseTranslateService $translate = null;

    protected Collection $localTranslations;

    protected Collection $providerTranslations;

    protected Collection $allTranslations;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->sync = app(SyncManager::class)
            ->withCommand($this)
            ->getService()
            ?->setUp();

        if ($this->option('translate')) {
            $this->translate = app(TranslateManager::class)
                ->withCommand($this)
                ->getService()
                ?->setUp();
        }

        $this->info('Syncing translations with the following configuration:');
        $this->table([
            'Sync Provider',
            'Update Sync Provider',
            'Translate',
            'Translate Provider',
        ], [
            [
                $this->sync->getName(),
                $this->option('retrieve-only') ? 'No' : 'Yes',
                $this->translate ? 'Yes' : 'No',
                $this->translate?->getName() ?? '-',
            ],
        ], 'box');

        if ($this->option('force')) {
            $this->warn('"force" flag passed, skipping confirmation dialog');
        } elseif (!$this->confirm('Do you wish to continue?')) {
            $this->info('Aborted');
            return;
        }

        $this->getLocalTranslations();
        $this->getProviderTranslations();
        $this->getAllTranslations();
        $this->retrieveMissingTranslations();
        $this->writeToProvider();
        $this->writeToFiles();
    }

    /**
     * Get all current translations in the code.
     */
    protected function getLocalTranslations(): void
    {
        $translations = collect(LaravelTranslationsSync::getAllTranslations())
            ->mapWithKeys(fn ($translation, $key) => Arr::dot($translation, $key . '::'))
            ->filter()
            ->mapWithKeys([$this->sync, 'mapSourceTranslation'])
            ->values();

        $this->info(sprintf('Found %s translations locally', $translations->count()));

        $this->localTranslations = $translations;
    }

    protected function getProviderTranslations(): void
    {
        $translations = $this->sync->getTranslations();

        $this->info(sprintf('Found %s translations at provider', $translations->count()));

        $this->providerTranslations = $translations;
    }

    /**
     * Combine target and source translation collections into one collection.
     */
    protected function getAllTranslations(): void
    {
        $allTranslations = $this->providerTranslations
            ->merge($this->localTranslations)
            // Base collection contains the target translations, so in case of double entries the target entry
            // is kept, thus preserving changes from the spreadsheet.
            ->unique($this->sync->getBaseKey())
            // Only keep translations that are currently in the code.
            ->filter(
                fn ($translation) => $this->sync->filterTargetTranslation($translation, $this->localTranslations)
            )
            ->sortBy($this->sync->getBaseKey())
            ->values();

        $allTranslations = $this->sync->parseAllTranslations($allTranslations, $this->localTranslations);

        $this->info(sprintf('Found %s total translations', $allTranslations->count()));

        $this->allTranslations = $allTranslations;
    }

    /**
     * Check for values that need to be translated.
     */
    protected function retrieveMissingTranslations(): void
    {
        if (!$this->option('translate')) {
            return;
        }

        $this->allTranslations = $this->translate->translateAll(
            $this->allTranslations,
            $this->sync->getTranslationKeys(),
            $this->sync->getBaseTranslationKey(),
            $this->sync->getBaseKey(),
        );
    }

    protected function writeToProvider(): void
    {
        if ($this->option('retrieve-only')) {
            return;
        }

        $this->sync->write($this->allTranslations);
    }

    /**
     * Write all the translations to the translation files.
     */
    protected function writeToFiles(): void
    {
        $translations = $this->allTranslations
            // Map all the translation values to their respective headings.
            ->map([$this->sync, 'mapTranslationForWrite'])
            // Reduce the translations to an array keyed by locale, filename, translation key and value.
            ->reduce(function (array $carry, array $translations) {
                $key = $translations[$this->sync->getBaseKey()];
                [$filename, $translationKey] = explode('::', $key, 2);

                unset($translations[$this->sync->getBaseKey()]);

                foreach ($translations as $locale => $value) {
                    Arr::set($carry[strtolower($locale)][$filename], $translationKey, $value);
                }

                return $carry;
            }, []);

        $updatedFiles = 0;

        // Write the translations to the files.
        foreach ($translations as $locale => $files) {
            // Only write translations for the configured locales.
            if (!LaravelTranslationsSync::localeIsAllowed($locale)) {
                continue;
            }

            foreach ($files as $filename => $lines) {
                if (str_ends_with($filename, '.json')) {
                    $this->writeJson($locale, $lines);
                }
                if (str_ends_with($filename, '.php')) {
                    $this->writePhp($locale, $filename, $lines);
                }

                $updatedFiles++;
            }
        }

        $this->info(sprintf(
            'Successfully written %s translations and updated %s files locally',
            $this->allTranslations->count(),
            $updatedFiles
        ));
    }

    /**
     * Write the translations to the JSON files.
     */
    protected function writeJson(string $locale, array $lines): void
    {
        $directory = lang_path();

        // The translation directory doesn't exist, so create it.
        if (!File::exists($directory)) {
            File::makeDirectory($directory, recursive: true);
        }

        $filePath = sprintf('%s/%s.json', $directory, $locale);
        $encoded = json_encode($lines, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (File::exists($filePath)) {
            File::replace($filePath, $encoded);
        } else {
            File::put($filePath, $encoded);
        }
    }

    /**
     * Write the translations to the PHP files.
     */
    protected function writePhp(string $locale, string $filename, array $lines): void
    {
        $directory = sprintf('%s/%s', lang_path(), $locale);

        // The translation directory doesn't exist, so create it.
        if (!File::exists($directory)) {
            File::makeDirectory($directory, recursive: true);
        }

        $filePath = sprintf('%s/%s', $directory, $filename);
        $content = '';

        // Parse each value and add it to the content.
        foreach ($lines as $key => $value) {
            $content .= $this->valueToPhpContent($key, $value);
        }

        if (empty($content)) {
            $this->warn(sprintf('Writing empty file: %s', $filePath));
        }

        if (File::exists($filePath)) {
            File::replace($filePath, sprintf("<?php\n\nreturn [\n\n%s\n];\n", $content));
        } else {
            File::put($filePath, sprintf("<?php\n\nreturn [\n\n%s\n];\n", $content));
        }
    }

    /**
     * Parse the given value to a content string.
     */
    protected function valueToPhpContent(string $key, string|array $value, int $depth = 1): string
    {
        $currentValue = '[';

        // Return an empty string if the key doesn't contain any values. We do this to prevent empty translations
        // from being added, so that in case of absence it always defaults to the default language's translation.
        if (is_array($value) && empty(array_filter($value))) {
            return '';
        }

        if (is_string($value)) {
            $quote = "'";

            // Change quote to double quote in case the value contains a single quote.
            if (str_contains($value, "'")) {
                $quote = '"';
            }

            // Check if the value contains quotes that are the same as the encapsulating quotes.
            // If that is the case, escape them in order to prevent problems.
            if (str_contains($value, $quote)) {
                $value = str_replace($quote, '\\' . $quote, $value);
            }

            // Return an empty string if the value is empty. We do this to prevent empty translations
            // from being added, so that in case of absence it always defaults to the default language's translation.
            if (empty($value)) {
                return '';
            }

            $currentValue = "{$quote}{$value}{$quote},";
        }

        $content = sprintf(
            "%s'%s' => %s\n",
            str_repeat(' ', 4 * $depth),
            $key,
            $currentValue
        );

        if (is_array($value)) {
            foreach ($value as $innerKey => $innerValue) {
                $content .= $this->valueToPhpContent($innerKey, $innerValue, $depth + 1);
            }

            $content .= sprintf(
                "%s],\n",
                str_repeat(' ', 4 * $depth)
            );
        }

        return $content;
    }
}
