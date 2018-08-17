<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/class/PortChecker.class.php';
require_once __DIR__ . '/class/MinecraftServerStatus.class.php';
require_once __DIR__ . '/class/MonitorManager.class.php';

function logging ($level, $msg)
{
    echo "[" . date('Y-m-d H:i:s') . "][" . $level . "] " . $msg . " \n";
}