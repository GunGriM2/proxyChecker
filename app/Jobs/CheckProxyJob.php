<?php

namespace App\Jobs;

use App\ProxyCheck;
use App\ProxyResult;
use App\Services\ProxyCheckService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckProxyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $proxyResult;

    public function __construct(ProxyResult $proxyResult)
    {
        $this->proxyResult = $proxyResult;
    }

    public function handle(ProxyCheckService $proxyCheckService)
    {
        $result = $proxyCheckService->checkProxy($this->proxyResult->proxy);

        // Сохраняем результат в БД
        $this->proxyResult->update(array_merge($result, ['completed' => true]));
        $this->proxyResult->save();
    }
}
