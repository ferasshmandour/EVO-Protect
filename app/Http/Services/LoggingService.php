<?php

namespace App\Http\Services;

use Illuminate\Http\Request;
use App\Models\SystemLog;

class LoggingService
{
    public function addLog(Request $request, $message)
    {
        $ip = $request->ip();
        $body = $request->getContent();
        $endpoint = $request->path();

        return SystemLog::create([
            'endpoint' => $endpoint,
            'body' => $body,
            'response' => $message,
            'ip' => $ip,
        ]);
    }
}
