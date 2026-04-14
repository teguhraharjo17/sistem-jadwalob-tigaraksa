<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MileniaApiService
{
    protected string $baseUrl;
    protected string $token;

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
        return Cache::remember('milenia_holidays', now()->addDay(), function () {
            try {
                $response = Http::withToken($this->token)
                    ->acceptJson()
                    ->get("{$this->baseUrl}/libur");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error("Milenia API error: " . $response->status() . " - " . $response->body());
                return [];
            } catch (\Exception $e) {
                Log::error("Milenia API Exception: " . $e->getMessage());
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
