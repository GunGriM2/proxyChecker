<?php

namespace App\Http\Controllers;

use App\Jobs\CheckProxyJob;
use App\ProxyCheck;
use App\ProxyResult;
use App\Services\ProxyCheckService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $proxyResults =[];

        // Создаем запись для общей проверки
        $proxyCheck = ProxyCheck::create(['status' => 'pending']);

        // Созадем записи для результата проверки кажого прокси
        foreach ($proxies as $proxy) {
            $proxyResult = ProxyResult::create(['proxy' => $proxy, 'proxy_check_id' => $proxyCheck->id, 'status' => false, 'completed' => false]);
            $proxyResults[] = $proxyResult;
        }

        // Запускаем джобы для кажого прокси
        foreach ($proxyResults as $proxyResult) {
            CheckProxyJob::dispatch($proxyResult);
        }

        // Ждем завершения всех заданий
        while (true) {
            $completedCount = ProxyResult::where('proxy_check_id', $proxyCheck->id)
                ->where('completed', true)
                ->count();
            $totalCount = ProxyResult::where('proxy_check_id', $proxyCheck->id)
                ->count();

            if ($totalCount === 0 || $completedCount === $totalCount) {
                break;
            }

            sleep(1);
        }

        // Общая проверка закончена
        $proxyCheck->completed = true;
        $proxyCheck->save();

        $results = ProxyResult::where('proxy_check_id', $proxyCheck->id)->get();

        $proxyCount = $results->count();
        $activeProxyCount = $results->reduce(function ($count, $proxyData) {
            return $count + ($proxyData['status'] ? 1 : 0);
        });

        $stats = [
            'count' => $proxyCount,
            'active_count' => $activeProxyCount,
        ];

        $results = $results->toArray();

        return response()->json(['success' => true, 'results' => $results, 'stats' => $stats]);
    }
}
