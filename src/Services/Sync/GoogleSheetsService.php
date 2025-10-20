<?php

namespace VanOns\LaravelTranslationsSync\Services\Sync;

use Google\Service\Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use VanOns\LaravelTranslationsSync\Exceptions\SyncException;
use VanOns\LaravelTranslationsSync\Facades\LaravelTranslationsSync;

class GoogleSheetsService extends BaseSyncService
{
    protected static string $name = 'Google Sheets';

    protected string $separator;

    protected GoogleSheetsAdapter $sheets;

    /**
     * The ID of the Google Spreadsheet to sync with. This can be found in the URL:
     * https://docs.google.com/spreadsheets/d/<spreadsheetId>
     */
    protected ?string $spreadsheetId = null;

    /**
     * The name of the sheet in the spreadsheet to sync with. This is the name that is displayed on the tab.
     */
    protected ?string $sheetName = null;

    /**
     * The first column in the table.
     */
    protected ?string $firstColumn = null;

    /**
     * The last column in the table.
     */
    protected ?string $lastColumn = null;

    /**
     * The row containing the table heading.
     */
    protected ?int $headingRow = null;

    /**
     * The row where the translations start.
     */
    protected ?int $firstRow = null;

    /**
     * The value of the heading cell of the first column that defines the translation key (default: 'KEY').
     */
    protected string $keyCellValue = 'KEY';

    /**
     * The value of the heading cell of the second column that defines the base translation (default: 'EN').
     */
    protected string $baseTranslationCellValue = 'EN';

    protected Collection $headings;

    /**
     * @throws SyncException
     */
    public function setUp(): static
    {
        $this->separator = LaravelTranslationsSync::getSeparator();

        $this->sheets = new GoogleSheetsAdapter();

        $config = config('translations-sync.sync_providers.google_sheets.sheet');

        $this->spreadsheetId = Arr::get($config, 'spreadsheet_id', $this->spreadsheetId);
        $this->sheetName = Arr::get($config, 'sheet_name', $this->sheetName);
        $this->firstColumn = Arr::get($config, 'first_column', $this->firstColumn);
        $this->lastColumn = Arr::get($config, 'last_column', $this->lastColumn);
        $this->headingRow = Arr::get($config, 'heading_row', $this->headingRow);
        $this->firstRow = Arr::get($config, 'first_row', $this->firstRow);
        $this->keyCellValue = Arr::get($config, 'key_cell_value', $this->keyCellValue);
        $this->baseTranslationCellValue = Arr::get($config, 'base_translation_cell_value', $this->baseTranslationCellValue);

        if (!$this->isEnabled()) {
            throw SyncException::providerNotConfigured();
        }

        $this->sheets->setSpreadsheetId($this->spreadsheetId);
        $this->getHeadings();

        return $this;
    }

    public function isEnabled(): bool
    {
        return !empty($this->spreadsheetId)
            && !empty($this->sheetName)
            && !empty($this->firstColumn)
            && !empty($this->lastColumn)
            && !empty($this->headingRow)
            && !empty($this->firstRow)
            && !empty($this->keyCellValue)
            && !empty($this->baseTranslationCellValue);
    }

    /**
     * Retrieve all the headings from the sheet.
     *
     * @throws SyncException
     */
    protected function getHeadings(): Collection
    {
        $headings = $this->sheets->readFromSheet(sprintf(
            '%s!%s%s:%s%s',
            $this->sheetName,
            $this->firstColumn,
            $this->headingRow,
            $this->lastColumn,
            $this->headingRow
        ));
        $headings = collect($headings)->collapse();

        if ($headings->isEmpty()) {
            throw SyncException::setupFailed('No headings found in sheet');
        }

        $this->headings = $headings;

        return $headings;
    }

    /**
     * Retrieve all the existing translations.
     */
    public function getTranslations(): Collection
    {
        $translations = $this->sheets->readFromSheet(sprintf(
            '%s!%s%s:%s',
            $this->sheetName,
            $this->firstColumn,
            $this->firstRow,
            $this->lastColumn
        ));

        return $this->translations = collect($translations)
            ->map(
                fn ($translation) => $this->headings
                    ->map(fn ($heading, $key) => [$heading => $translation[$key] ?? null])
                    ->collapse()
                    ->toArray()
            );
    }

    public function mapSourceTranslation(mixed $translation, string|int $key): array
    {
        return [
            $key => [
                $this->keyCellValue => $key,
                $this->baseTranslationCellValue => $translation,
            ],
        ];
    }

    public function getTranslationKeys(): Collection
    {
        return $this->headings;
    }

    public function getBaseTranslationKey(): string
    {
        return $this->baseTranslationCellValue;
    }

    public function getBaseKey(): string
    {
        return $this->keyCellValue;
    }

    public function filterTargetTranslation(array $translation, Collection $localTranslations): bool
    {
        return in_array(
            $translation[$this->keyCellValue],
            $localTranslations->pluck($this->keyCellValue)->toArray(),
            true
        );
    }

    public function parseAllTranslations(Collection $translations, Collection $localTranslations): Collection
    {
        // Retrieve all local translations for all configured locales.
        $allLocalTranslations = collect(LaravelTranslationsSync::getLocales())
            ->mapWithKeys(fn ($locale) => [$locale => LaravelTranslationsSync::getTranslationsForLocale($locale)]);

        return $translations
            // Make sure all cells are filled, either with an empty string or a value.
            ->map(
                function ($translation) use ($allLocalTranslations) {
                    // Also add array items for languages that don't have a value yet in order to clear cells
                    // in the spreadsheet that need to be cleared.
                    $translation = array_merge(
                        $this->headings->slice(1)
                            ->mapWithKeys(fn ($heading) => [$heading => ''])
                            ->toArray(),
                        $translation
                    );

                    $key = $translation[$this->getBaseKey()];
                    [$filename, $translationKey] = explode($this->separator, $key, 2);

                    // If a value is empty, and the locale is allowed, fill it with the local value.
                    foreach ($translation as $heading => $value) {
                        if (empty($value) && $heading !== $this->getBaseKey() && LaravelTranslationsSync::localeIsAllowed($heading)) {
                            $translation[$heading] = lts_array_get($allLocalTranslations[$heading][$filename] ?? [], $translationKey, '', $this->separator);
                        }
                    }

                    return $translation;
                }
            )
            // Replace the array keys by indexes, because that's how the API expects it.
            ->map(
                fn ($translation) => collect($translation)
                    ->mapWithKeys(fn ($item, $key) => [$this->headings->search($key) => $item])
                    ->sortKeys()
            );
    }

    public function mapTranslationForWrite(Collection $translation): array
    {
        $result = [];

        foreach ($this->headings->toArray() as $i => $heading) {
            $result[$heading] = $translation[$i];
        }

        return $result;
    }

    /**
     * Clear all translations so that unwanted entries don't stay behind.
     *
     * @throws Exception
     */
    public function prepareForWrite(): void
    {
        $this->sheets->clearRange(sprintf(
            '%s!%s%s:%s',
            $this->sheetName,
            $this->firstColumn,
            $this->firstRow,
            $this->lastColumn
        ));

        $this->info('Sheet prepared for write');
    }

    /**
     * Update the sheet with the new translations.
     *
     * @throws Exception
     */
    public function write(Collection $translations): void
    {
        $translations = $translations
            ->mapWithKeys(function ($translations, $key) {
                $translationKeyIndex = $this->headings->search($this->getBaseKey());
                $translationKey = $translations[$translationKeyIndex];

                foreach ($translations as $localeIndex => $value) {
                    $locale = $this->headings[$localeIndex];

                    // If the value is not for the base key, or for an allowed locale, override it with the
                    // original value from the provider.
                    if ($locale !== $this->getBaseKey() && !LaravelTranslationsSync::localeIsAllowed($locale)) {
                        $translations[$localeIndex] = $this->translations->firstWhere($this->getBaseKey(), '=', $translationKey)[$locale] ?? '';
                    }
                }

                return [$key => $translations];
            });

        $this->prepareForWrite();

        $updatedCells = $this->sheets->writeToSheet(
            sprintf(
                '%s!%s%s',
                $this->sheetName,
                $this->firstColumn,
                $this->firstRow
            ),
            $translations->toArray()
        );

        $this->info(sprintf(
            'Successfully written %s translations and updated %s cells',
            $translations->count(),
            $updatedCells
        ));
    }
}
