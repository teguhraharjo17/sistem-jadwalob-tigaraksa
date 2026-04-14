<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MileniaApiService
{
    protected ?string $baseUrl;
    protected ?string $token;
    protected ?string $lastError = null;

    public function __construct()
    {
        $this->baseUrl = config('services.milenia.url');
        $this->token = config('services.milenia.token');
    }

    /**
     * Fetch holidays from external API.
     *
     * @return array
     */
    public function getHolidays(): array
    {
        $this->lastError = null;

        if (!$this->baseUrl || !$this->token) {
            $this->lastError = 'Milenia API configuration is incomplete. URL or token is missing.';
            Log::warning($this->lastError);
            return [];
        }

        if (Cache::has('milenia_holidays')) {
            return Cache::get('milenia_holidays', []);
        }

        Log::info("Attempting to fetch holidays from Milenia API: {$this->baseUrl}");

        try {
            $allData = [];
            $currentPage = 1;
            $lastPage = 1;

            do {
                Log::info("Fetching page {$currentPage} from Milenia API...");

                $startTime = microtime(true);
                $response = Http::timeout(10)->withToken($this->token)
                    ->acceptJson()
                    ->get("{$this->baseUrl}/libur", ['page' => $currentPage]);
                $duration = round(microtime(true) - $startTime, 2);

                if (!$response->successful()) {
                    $this->lastError = "Milenia API returned HTTP {$response->status()} on page {$currentPage}.";
                    Log::error($this->lastError . ' Body: ' . $response->body());
                    return [];
                }

                $json = $response->json();

                Log::info("Page {$currentPage} fetched successfully in {$duration}s. Records in page: " . count($json['data'] ?? []));

                $pageData = isset($json['data']) && is_array($json['data']) ? $json['data'] : [];
                $allData = array_merge($allData, $pageData);

                $lastPage = $json['last_page'] ?? 1;
                $currentPage++;
            } while ($currentPage <= $lastPage);

            Log::info("Total holidays fetched: " . count($allData));

            if (!empty($allData)) {
                Cache::put('milenia_holidays', $allData, now()->addDays(7));
            } else {
                $this->lastError = 'Milenia API returned an empty holiday dataset.';
                Log::warning($this->lastError);
            }

            return $allData;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            Log::error("Milenia API Exception (Server IP: " . request()->ip() . "): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear the holidays cache.
     *
     * @return void
     */
    public function clearHolidaysCache(): void
    {
        Cache::forget('milenia_holidays');
    }

    public function getDiagnostics(int $count = 0): array
    {
        return [
            'url' => $this->baseUrl,
            'token_configured' => filled($this->token),
            'cached' => Cache::has('milenia_holidays'),
            'count' => $count,
            'server_time' => now()->toDateTimeString(),
            'last_error' => $this->lastError,
        ];
    }
}
