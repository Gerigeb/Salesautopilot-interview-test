<?php

namespace App\Services;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ApiClient
{
    private const TOKEN_CACHE_KEY = 'salesautopilot_jwt';

    private const TOKEN_CACHE_MINUTES = 30;

    public function __construct(
        private string $baseUrl,
        private string $username,
        private string $password,
        private CacheRepository $cache,
    ) {}

    /**
     * @param string $path
     * @param array<string, mixed> $query
     * @return array
     * @throws ConnectionException
     * @throws RequestException
     */
    public function get(string $path, array $query = []): array
    {
        return $this->request($path, $query)->json();
    }

    /**
     * @param string $path
     * @param array<string, mixed> $query
     * @return Response
     * @throws ConnectionException
     * @throws RequestException
     */
    private function request(string $path, array $query = []): Response
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout(30)
            ->connectTimeout(10)
            ->withToken($this->getToken())
            ->get($path, $query)
            ->throw();
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    private function getToken(): string
    {
        return $this->cache->remember(
            self::TOKEN_CACHE_KEY,
            now()->addMinutes(self::TOKEN_CACHE_MINUTES),
            fn () => $this->login(),
        );
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    private function login(): string
    {
        $response = Http::baseUrl($this->baseUrl)
            ->timeout(30)
            ->connectTimeout(10)
            ->post('/access-token', [
                'username' => $this->username,
                'password' => $this->password,
            ])
            ->throw();

        return $response->json('token');
    }
}
