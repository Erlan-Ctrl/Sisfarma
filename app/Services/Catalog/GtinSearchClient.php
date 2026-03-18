<?php

namespace App\Services\Catalog;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GtinSearchClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $token = null,
        private readonly int $timeoutSeconds = 6,
        private readonly int $cacheDays = 60,
    ) {
    }

    /**
     * @return array{ean:string, gtin14:string, name:string, brand:?string, size:?string, raw: array<string, mixed>}|null
     */
    public function lookup(string $code): ?array
    {
        $digits = preg_replace('/\\D+/', '', $code) ?? '';
        if ($digits === '') {
            return null;
        }
        if (strlen($digits) < 8 || strlen($digits) > 14) {
            return null;
        }

        $gtin14 = str_pad($digits, 14, '0', STR_PAD_LEFT);
        $cacheKey = "gtinsearch:item:{$gtin14}";

        /** @var array{ean:string, gtin14:string, name:string, brand:?string, size:?string, raw: array<string, mixed>}|null */
        return Cache::remember($cacheKey, now()->addDays(max(1, $this->cacheDays)), function () use ($digits, $gtin14) {
            $endpoint = rtrim($this->baseUrl, '/').'/items/'.$gtin14;

            $req = Http::acceptJson()
                ->asJson()
                ->timeout(max(1, $this->timeoutSeconds));

            if (is_string($this->token) && trim($this->token) !== '') {
                $req = $req->withHeader('Authorization', 'Token token='.$this->token);
            }

            $res = $req->get($endpoint);
            if (! $res->successful()) {
                return null;
            }

            $payload = $res->json();
            if (! is_array($payload) || $payload === []) {
                return null;
            }

            $item = $payload[0] ?? null;
            if (! is_array($item)) {
                return null;
            }

            $brand = trim((string) ($item['brand_name'] ?? ''));
            $name = trim((string) ($item['name'] ?? ''));
            $size = trim((string) ($item['size'] ?? ''));

            $fullName = trim(($brand !== '' ? $brand.' ' : '').$name);
            $fullName = preg_replace('/\\s+/', ' ', $fullName) ?? $fullName;
            $fullName = trim($fullName, " \t\n\r\0\x0B-");

            // Prefer the scanned representation when it already looks like EAN-13/GTIN-14.
            $ean = $digits;
            if (strlen($digits) < 13) {
                $apiGtin14 = trim((string) ($item['gtin14'] ?? $gtin14));
                if ($apiGtin14 !== '') {
                    // Common case: GTIN-14 has leading zeros for EAN-13.
                    $ean = ltrim($apiGtin14, '0');
                    if ($ean === '') {
                        $ean = $apiGtin14;
                    }
                }
            }

            if ($fullName === '') {
                $fullName = 'Produto '.$ean;
            }

            return [
                'ean' => $ean,
                'gtin14' => (string) ($item['gtin14'] ?? $gtin14),
                'name' => $fullName,
                'brand' => $brand !== '' ? $brand : null,
                'size' => $size !== '' ? $size : null,
                'raw' => $item,
            ];
        });
    }
}
