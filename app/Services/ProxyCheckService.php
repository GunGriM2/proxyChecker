<?php

namespace App\Services;

class ProxyCheckService
{
    public function checkProxy(string $proxy): array
    {
        if (!$this->isValidProxy($proxy)) {
            return  ['status' => false];
        }

        [$ip, $port] = explode(':', $proxy);

        return ['status' => true, 'ip' => $ip, 'port' => $port];
    }

    private function isValidProxy(string $proxy): bool
    {
        // Проверяем, соответствует ли строка формату IP:PORT
        return preg_match('/^(\d{1,3}\.){3}\d{1,3}:\d{1,5}$/', trim($proxy));
    }
}
