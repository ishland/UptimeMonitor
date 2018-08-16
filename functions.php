<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/class/PortChecker.class.php';
require_once __DIR__ . '/class/MinecraftServerStatus.class.php';
require_once __DIR__ . '/class/MonitorManager.class.php';

function allocatePorts ()
{
    global $monitorport;
    $check = new PortChecker();
    for ($i = 40000; $i <= 65536; $i ++) {
        $monitorport = $i;
        echo "[" . date('Y-m-d H:i:s') .
                "][Main][Init][Info] Allocating port {$monitorport} to monitor... ";
        if ($check->check("127.0.0.1", $monitorport) == 1 or
                $check->check("127.0.0.1", $monitorport) == 0) {
            echo "failed.\n";
            continue;
        }
        echo "success.\n";
        break;
    }
    exit(
            date('Y-m-d H:i:s') .
            "][Main][Init][Fatal Error] Failed to allocate port to monitor.\n");
}
