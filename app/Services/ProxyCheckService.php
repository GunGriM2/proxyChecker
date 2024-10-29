<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\CheckProxyJob;
use App\ProxyCheck;
use App\ProxyResult;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Collection;

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

    public function handleProxies(array $proxies)
    {
        // Создаем запись для общей проверки
        $proxyCheck = ProxyCheck::create(['status' => 'pending']);
        $proxyResults = [];

        // Создаем записи для результата проверки каждого прокси
        foreach ($proxies as $proxy) {
            $proxyResult = ProxyResult::create(['proxy' => $proxy, 'proxy_check_id' => $proxyCheck->id, 'status' => false, 'completed' => false]);
            $proxyResults[] = $proxyResult;
        }

        // Запускаем джобы для каждого прокси
        foreach ($proxyResults as $proxyResult) {
            CheckProxyJob::dispatch($proxyResult);
        }

        // Ждем завершения всех заданий
        $this->waitForCompletion($proxyCheck->id);

        // Общая проверка закончена
        $proxyCheck->completed = true;
        $proxyCheck->save();

        return $proxyCheck->id;
    }

    private function waitForCompletion(int $proxyCheckId)
    {
        while (true) {
            $completedCount = ProxyResult::where('proxy_check_id', $proxyCheckId)
                ->where('completed', true)
                ->count();
            $totalCount = ProxyResult::where('proxy_check_id', $proxyCheckId)
                ->count();

            if ($totalCount === 0 || $completedCount === $totalCount) {
                break;
            }

            sleep(1);
        }
    }

    public function getProxyStats(Collection $results): array
    {
        $proxyCount = $results->count();
        $activeProxyCount = $results->where('status', true)->count();

        return [
            'count' => $proxyCount,
            'active_count' => $activeProxyCount,
        ];
    }
}
