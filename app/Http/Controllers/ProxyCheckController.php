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
        $proxies = explode("\n", $request->input('proxies'));
        debug($proxies);
        $results = [];

        foreach ($proxies as $proxy) {
            $data = $this->proxyCheckService->checkProxy($proxy);
            $results[] = $proxy . ' - ' . print_r($data, true);
        }

        return response()->json(['results' => $results]);
    }
}
