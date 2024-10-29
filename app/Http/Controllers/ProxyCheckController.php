<?php

namespace App\Http\Controllers;

use App\Jobs\CheckProxyJob;
use App\ProxyCheck;
use App\ProxyResult;
use App\Services\ProxyCheckService;
use Illuminate\Http\Request;

class ProxyCheckController extends Controller
{
    private $proxyCheckService;

    public function __construct(
        ProxyCheckService $proxyCheckService
    ) {
        $this->proxyCheckService = $proxyCheckService;
    }

    public function check(Request $request) {
        $request->validate([
            'proxies' => 'required|string',
        ]);

        $proxies = explode("\n", $request->input('proxies'));

        // Используем новый сервис для обработки прокси
        $proxyCheckId = $this->proxyCheckService->handleProxies($proxies);

        // Получаем результаты проверки
        $proxyResults = ProxyResult::where('proxy_check_id', $proxyCheckId)->get();

        // Получаем статистику
        $stats = $this->proxyCheckService->getProxyStats($proxyResults);

        return response()->json(['success' => true, 'results' => $proxyResults, 'stats' => $stats]);
    }
}
