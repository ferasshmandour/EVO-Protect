<?php

namespace App\Http\Services;

use Illuminate\Http\Request;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Log;
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
            'mac_address' => $this->getMacAddress($request),
        ]);
    }

    public function getMacAddress(Request $request): string
    {
        $wifiIp = getHostByName(getHostName());
        $clientIp = $request->ip();

        // Skip local requests
        if (in_array($clientIp, ['127.0.0.1', 'localhost', $wifiIp])) {
            return $this->getLocalMacAddress();
        }

        // Log client IP
        Log::info("Request from IP: $clientIp");

        // Ping client to populate ARP
        $pingCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? "ping -n 1 $clientIp"
            : "ping -c 1 $clientIp";
        $pingResult = shell_exec($pingCmd);
        Log::info("Ping result: $pingResult");

        // Check if ping succeeded
        if (strpos($pingResult, 'bytes=') === false && strpos($pingResult, 'TTL=') === false) {
            return "Ping failed. Client ($clientIp) may be unreachable.";
        }

        // Fetch ARP table
        $arpCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? "arp -a"
            : "arp -n";
        $arpOutput = shell_exec($arpCmd);
        Log::info("ARP Table: $arpOutput");

        // Parse ARP output
        $lines = explode("\n", $arpOutput);
        foreach ($lines as $line) {
            if (str_contains($line, $clientIp)) {
                // Linux pattern (192.168.10.15 aa:bb:cc:dd:ee:ff)
                if (preg_match('/^' . preg_quote($clientIp) . '\s+\S+\s+([A-Fa-f0-9:]+)/', $line, $matches)) {
                    return $matches[1];
                }
                // Windows pattern (192.168.10.15   aa-bb-cc-dd-ee-ff)
                if (preg_match('/' . preg_quote($clientIp) . '.*([A-Fa-f0-9-]{17})/', $line, $matches)) {
                    return strtoupper(str_replace('-', ':', $matches[1]));
                }
            }
        }

        return "MAC not found for $clientIp. Check ARP table manually.";
    }

    private function getLocalMacAddress(): string
    {
        $process = new Process([strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'getmac' : 'ifconfig']);
        $process->run();
        $output = $process->getOutput();
        preg_match('/(?:[A-Fa-f0-9]{2}[:-]){5}[A-Fa-f0-9]{2}/', $output, $matches);
        return $matches[0] ?? 'Local MAC not found';
    }
}
