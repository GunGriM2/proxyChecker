<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;

class ProxyCheckService
{
    public function checkProxy(string $proxy): array
    {
        if (!$this->isValidProxy($proxy)) {
            return [
                'proxy'  => $proxy,
                'status' => false
            ];
        }

        [$ip, $port] = explode(':', $proxy);

        $client = new Client([RequestOptions::TIMEOUT => 10]);
        $targetUrl = 'http://ip-api.com/json';

        $type = null;
        $city = null;
        $status = false;
        $speed = null;

        $httpFailed = false;
        $socksFailed = false;

        // Пытаемся проверить прокси через HTTP
        try {
            $response = $client->get($targetUrl, [
                RequestOptions::PROXY => 'http://' . $proxy,
                RequestOptions::VERIFY => false,
                RequestOptions::ON_STATS => function (TransferStats $stats) use (&$speed) {
                    $speed = $stats->getHandlerStat('speed_download');
                }
            ]);

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody());

                if ($data->query === $ip) {
                    debug($speed);
                    $speed = $speed * 8 / 1024;

                    $status = true;
                    $type = 'http';
                    $city = $data->city;
                    $speed = round($speed, 2) . ' kbps';
                } else {
                    $httpFailed = true;
                }

                debug($data);
            } else {
                $httpFailed = true;
            }
        } catch (GuzzleException $e) {
            debug($e->getMessage());
            $httpFailed = true;
        }


        if ($httpFailed) {
            try {
                $response = $client->get($targetUrl, [
                    RequestOptions::PROXY => 'socks5://' . $proxy,
                    RequestOptions::VERIFY => false,
                    RequestOptions::ON_STATS => function (TransferStats $stats) use (&$speed) {
                        $speed = $stats->getHandlerStat('speed_download');
                    }
                ]);

                if ($response->getStatusCode() === 200) {
                    $data = json_decode($response->getBody());

                    if ($data->query === $ip) {
                        debug($speed);
                        $speed = $speed * 8 / 1024;

                        $status = true;
                        $type = 'socks';
                        $city = $data->city;
                        $speed = round($speed, 2) . ' kbps';
                    } else {
                        $socksFailed = true;
                    }
                    debug($data);
                } else {
                    $socksFailed = true;
                }
            } catch (GuzzleException $e) {
                debug($e->getMessage());
                $socksFailed = true;
            }
        }

        if ($httpFailed && $socksFailed) {
            $status = false;
            $type = null;
        }

        return [
            'status' => $status,
            'proxy' => $proxy,
            'city' => $city,
            'type' => $type,
            'speed' => $speed,
        ];
    }

    private function isValidProxy(string $proxy): bool
    {
        // Проверяем, соответствует ли строка формату IP:PORT
        return preg_match('/^(\d{1,3}\.){3}\d{1,3}:\d{1,5}$/', trim($proxy));
    }
}
