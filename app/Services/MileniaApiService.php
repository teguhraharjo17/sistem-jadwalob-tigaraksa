<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MileniaApiService
{
    protected ?string $baseUrl;
    protected ?string $token;

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
        if (!$this->baseUrl || !$this->token) {
            Log::warning("Milenia API configuration is incomplete. URL or Token is missing.");
            return [];
        }

        return Cache::remember('milenia_holidays', now()->addDays(7), function () {
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
                        Log::error("Milenia API error on page {$currentPage} (Status: " . $response->status() . "): " . $response->body());
                        break;
                    }

                    Log::info("Page {$currentPage} fetched successfully in {$duration}s. Records in page: " . count($response->json()['data'] ?? []));

                    $json = $response->json();
                    
                    // Extract data array
                    $pageData = isset($json['data']) && is_array($json['data']) ? $json['data'] : [];
                    $allData = array_merge($allData, $pageData);

                    // Handle pagination logic
                    $lastPage = $json['last_page'] ?? 1;
                    $currentPage++;

                } while ($currentPage <= $lastPage);

                Log::info("Total holidays fetched: " . count($allData));
                return $allData;

            } catch (\Exception $e) {
                Log::error("Milenia API Exception (Server IP: " . request()->ip() . "): " . $e->getMessage());
                return [];
            }
        });
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
}
