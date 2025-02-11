<?php

namespace App\Http\Services;

use Illuminate\Http\Request;
use App\Models\SystemLog;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class LoggingService
{
    public function addLog(Request $request, $message)
    {
        $ip = $request->ip();
        $endpoint = $request->path();

        if ($request->isJson()) {
            $body = $request->getContent();
        } elseif ($request->isMethod('GET')) {
            $body = json_encode($request->query());
        } elseif ($request->isMethod('POST')) {
            $body = json_encode($request->post());
        } else {
            $body = json_encode($request->all());
        }

        return SystemLog::create([
            'endpoint' => $endpoint,
            'body' => $body,
            'response' => $message,
            'ip' => $ip,
            'mac_address' => $this->getMacAddress(),
        ]);
    }


    public function getMacAddress(): string
    {
        $process = new Process(['getmac']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();
        preg_match('/([A-Fa-f0-9]{2}(-|:)){5}[A-Fa-f0-9]{2}/', $output, $matches);

        return $matches[0] ?? 'MAC Address not found';
    }
}
