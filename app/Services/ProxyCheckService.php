<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;

class ProxyCheckService
{
    private $targetUrl;
    private $client;

    public function __construct()
    {
        $this->client = new Client([RequestOptions::TIMEOUT => 10]);
        $this->targetUrl = 'http://ip-api.com/json';
    }

    public function checkProxy(string $proxy): array
    {
        if (!$this->isValidProxy($proxy)) {
            return $this->formatResult($proxy, false);
        }

        $result = $this->checkProxyConnection($proxy, 'http');

        if (!$result['status']) {
            $result = $this->checkProxyConnection($proxy, 'socks5');
        }

        return $result;
    }

    private function checkProxyConnection(string $proxy, string $type): array
    {
        $speed = null;

        try {
            $response = $this->client->get($this->targetUrl, [
                RequestOptions::PROXY => "{$type}://{$proxy}",
                RequestOptions::VERIFY => false,
                RequestOptions::ON_STATS => function (TransferStats $stats) use (&$speed) {
                    $speed = $this->calculateSpeed($stats->getHandlerStat('speed_download'));
                }
            ]);

            $data = json_decode($response->getBody()->getContents());

            if ($response->getStatusCode() === 200 && $data->query === explode(':', $proxy)[0]) {
                return $this->formatResult($proxy, true, $type, $data->city, $speed);
            }
        } catch (GuzzleException $e) {

        }

        return $this->formatResult($proxy, false);
    }

    private function calculateSpeed(?float $speed): ?string
    {
        return $speed ? round(($speed * 8) / 1024, 2) . ' kbps' : null;
    }

    private function formatResult(string $proxy, bool $status, string $type = null, string $city = null, string $speed = null): array
    {
        return [
            'proxy' => $proxy,
            'status' => $status,
            'type' => $type,
            'city' => $city,
            'speed' => $speed,
        ];
    }

    private function isValidProxy(string $proxy): bool
    {
        return (bool) preg_match('/^(\d{1,3}\.){3}\d{1,3}:\d{1,5}$/', trim($proxy));
    }
}
