<?php

namespace VanOns\LaravelTranslationsSync\Services\Sync;

use Exception;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ClearValuesRequest;
use Google_Service_Sheets_ValueRange;

class GoogleSheetsAdapter
{
    protected Google_Service_Sheets $service;

    protected string $spreadsheetId;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $client = new Google_Client();
        $client->setApplicationName(config('app.name'));
        $client->setAuthConfig(config('translations-sync.sync_providers.google_sheets.auth_config'));
        $client->setScopes(config('translations-sync.sync_providers.google_sheets.scopes', []));

        $this->service = new Google_Service_Sheets($client);
    }

    public function setSpreadsheetId(string $spreadsheetId): static
    {
        $this->spreadsheetId = $spreadsheetId;

        return $this;
    }

    public function readFromSheet(string|array $range): array
    {
        if (is_array($range)) {
            $results = $this->service->spreadsheets_values->batchGet($this->spreadsheetId, ['ranges' => $range]);

            return $results->getValueRanges();
        }

        $results = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);

        return $results->getValues() ?? [];
    }

    /**
     * @throws \Google\Service\Exception
     */
    public function writeToSheet(string $range, array $values): int
    {
        $body = new Google_Service_Sheets_ValueRange([
            'values' => $values,
        ]);

        $params = [
            'valueInputOption' => 'RAW',
        ];

        $result = $this->service->spreadsheets_values->update($this->spreadsheetId, $range, $body, $params);

        return $result->getUpdatedCells();
    }

    /**
     * @throws \Google\Service\Exception
     */
    public function clearRange(string|array $range): void
    {
        if (is_array($range)) {
            $batchRange = new \Google_Service_Sheets_BatchClearValuesRequest();
            $batchRange->setRanges($range);

            $this->service->spreadsheets_values->batchClear(
                $this->spreadsheetId,
                $batchRange
            );
        } else {
            $this->service->spreadsheets_values->clear(
                $this->spreadsheetId,
                $range,
                new Google_Service_Sheets_ClearValuesRequest()
            );
        }
    }
}
