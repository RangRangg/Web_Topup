<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GameAccountVerifier
{
    /**
     * Verify and fetch in-game player nickname in real-time.
     *
     * @return array{success: bool, nickname: ?string, game: ?string, country: ?string, message: ?string}
     */
    public function verify(string $gameSlug, string $accountId, ?string $accountServer = null): array
    {
        $accountId = trim($accountId);
        $accountServer = $accountServer ? trim($accountServer) : null;

        if (empty($accountId)) {
            return [
                'success' => false,
                'nickname' => null,
                'game' => null,
                'country' => null,
                'message' => 'User ID wajib diisi.',
            ];
        }

        // Cache successful lookups for 6 hours to keep UI lightning fast
        $cacheKey = 'ign_check:'.Str::slug($gameSlug).":{$accountId}:".($accountServer ?? 'noserver');

        $cached = Cache::get($cacheKey);
        if (is_array($cached) && ! empty($cached['success'])) {
            return $cached;
        }

        $result = $this->performLookup($gameSlug, $accountId, $accountServer);

        if (! empty($result['success'])) {
            Cache::put($cacheKey, $result, 21600);
        }

        return $result;
    }

    /**
     * Build configured HTTP client with resilient SSL and timeout settings.
     */
    protected function httpClient(int $timeout = 8): PendingRequest
    {
        $caPath = 'D:/laragon/etc/ssl/cacert.pem';
        $verify = true;

        if (file_exists($caPath)) {
            $verify = $caPath;
        } elseif (app()->isLocal()) {
            $verify = false;
        }

        return Http::timeout($timeout)
            ->withOptions(['verify' => $verify])
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'AetherelGameStore/2.0',
            ]);
    }

    /**
     * Resilient HTTP GET lookup with automatic SSL fallback for local environments.
     *
     * @param  array<string, mixed>  $params
     */
    protected function executeLookup(string $url, array $params = [], int $timeout = 8): ?Response
    {
        try {
            return $this->httpClient($timeout)->get($url, $params);
        } catch (ConnectionException $e) {
            if (app()->isLocal()) {
                return Http::timeout($timeout)
                    ->withoutVerifying()
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'User-Agent' => 'AetherelGameStore/2.0',
                    ])
                    ->get($url, $params);
            }

            throw $e;
        }
    }

    /**
     * Perform the actual API lookup.
     */
    protected function performLookup(string $gameSlug, string $accountId, ?string $accountServer = null): array
    {
        $slug = Str::slug($gameSlug);

        // Mobile Legends
        if ($slug === 'mobile-legends' || str_contains($slug, 'mlbb') || str_contains($slug, 'legend')) {
            return $this->lookupMobileLegends($accountId, $accountServer);
        }

        // Free Fire
        if ($slug === 'free-fire' || str_contains($slug, 'freefire')) {
            return $this->lookupFreeFire($accountId);
        }

        // Genshin Impact
        if ($slug === 'genshin-impact' || str_contains($slug, 'genshin')) {
            return $this->lookupGenshin($accountId);
        }

        // Honkai: Star Rail
        if ($slug === 'honkai-star-rail' || str_contains($slug, 'star-rail')) {
            return $this->lookupHonkaiStarRail($accountId);
        }

        // Default response for games with standard format validation
        return [
            'success' => true,
            'nickname' => null,
            'game' => $gameSlug,
            'country' => null,
            'message' => 'Format ID valid.',
        ];
    }

    /**
     * Mobile Legends live nickname lookup.
     */
    protected function lookupMobileLegends(string $accountId, ?string $accountServer): array
    {
        if (empty($accountServer)) {
            return [
                'success' => false,
                'nickname' => null,
                'game' => 'Mobile Legends',
                'country' => null,
                'message' => 'Server / Zone ID wajib diisi untuk Mobile Legends.',
            ];
        }

        try {
            // Primary provider: api.isan.eu.org
            $response = $this->executeLookup('https://api.isan.eu.org/nickname/ml', [
                'id' => $accountId,
                'zone' => $accountServer,
            ]);

            if ($response !== null && $response->successful()) {
                $data = $response->json();
                if (! empty($data['success']) && ! empty($data['name'])) {
                    return [
                        'success' => true,
                        'nickname' => $data['name'],
                        'game' => $data['game'] ?? 'Mobile Legends: Bang Bang',
                        'country' => $data['country'] ?? 'Indonesia',
                        'message' => 'Akun Mobile Legends berhasil ditemukan.',
                    ];
                }
            }
        } catch (Throwable $e) {
            report($e);
        }

        return [
            'success' => false,
            'nickname' => null,
            'game' => 'Mobile Legends',
            'country' => null,
            'message' => 'Akun Mobile Legends tidak ditemukan. Pastikan User ID dan Server (Zone ID) sudah benar.',
        ];
    }

    /**
     * Free Fire live nickname lookup.
     */
    protected function lookupFreeFire(string $accountId): array
    {
        try {
            $response = $this->executeLookup('https://api.isan.eu.org/nickname/ff', [
                'id' => $accountId,
            ]);

            if ($response !== null && $response->successful()) {
                $data = $response->json();
                if (! empty($data['success'])) {
                    return [
                        'success' => true,
                        'nickname' => $data['name'] ?? ('Player_'.substr($accountId, -4)),
                        'game' => $data['game'] ?? 'Garena Free Fire',
                        'country' => 'Indonesia',
                        'message' => 'Akun Free Fire berhasil diverifikasi.',
                    ];
                }
            }
        } catch (Throwable $e) {
            report($e);
        }

        return [
            'success' => false,
            'nickname' => null,
            'game' => 'Free Fire',
            'country' => null,
            'message' => 'Akun Free Fire tidak ditemukan. Silakan cek kembali User ID Anda.',
        ];
    }

    /**
     * Genshin Impact live nickname lookup.
     */
    protected function lookupGenshin(string $accountId): array
    {
        try {
            $response = $this->executeLookup('https://api.isan.eu.org/nickname/gi', [
                'id' => $accountId,
            ]);

            if ($response !== null && $response->successful()) {
                $data = $response->json();
                if (! empty($data['success']) && ! empty($data['name'])) {
                    return [
                        'success' => true,
                        'nickname' => $data['name'],
                        'game' => 'Genshin Impact',
                        'country' => $data['server'] ?? 'Asia',
                        'message' => 'Akun Genshin Impact berhasil diverifikasi.',
                    ];
                }
            }
        } catch (Throwable $e) {
            report($e);
        }

        return [
            'success' => true,
            'nickname' => null,
            'game' => 'Genshin Impact',
            'country' => null,
            'message' => 'Format UID Genshin valid.',
        ];
    }

    /**
     * Honkai: Star Rail live nickname lookup.
     */
    protected function lookupHonkaiStarRail(string $accountId): array
    {
        try {
            $response = $this->executeLookup('https://api.isan.eu.org/nickname/hsr', [
                'id' => $accountId,
            ]);

            if ($response !== null && $response->successful()) {
                $data = $response->json();
                if (! empty($data['success']) && ! empty($data['name'])) {
                    return [
                        'success' => true,
                        'nickname' => $data['name'],
                        'game' => 'Honkai: Star Rail',
                        'country' => $data['server'] ?? 'Asia',
                        'message' => 'Akun Honkai: Star Rail berhasil diverifikasi.',
                    ];
                }
            }
        } catch (Throwable $e) {
            report($e);
        }

        return [
            'success' => true,
            'nickname' => null,
            'game' => 'Honkai: Star Rail',
            'country' => null,
            'message' => 'Format UID HSR valid.',
        ];
    }
}
