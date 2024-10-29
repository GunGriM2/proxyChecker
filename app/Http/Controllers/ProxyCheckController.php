<?php

namespace App\Http\Controllers;

use App\Services\ProxyCheckService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        debug($proxies);
        $results = [];

        foreach ($proxies as $proxy) {
            $proxyData = $this->proxyCheckService->checkProxy($proxy);
            $results[] = $proxyData;
        }

        $proxyCount = count($proxies);
        $activeProxyCount = array_reduce($results, function ($count, $proxyData) {
            return $count + ($proxyData['status'] ? 1 : 0);
        }, 0);

        $stats = [
            'count' => $proxyCount,
            'active_count' => $activeProxyCount,
        ];

        return response()->json(['success' => true, 'results' => $results, 'stats' => $stats]);
    }
}
