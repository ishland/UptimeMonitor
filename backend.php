<?php
require_once __DIR__ . '/Workerman/Autoloader.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/class/PortChecker.class.php';
require_once __DIR__ . '/class/MinecraftServerStatus.class.php';
require_once __DIR__ . '/class/MonitorManager.class.php';
use Workerman\Worker;

define("PROTOCOL_VERSION", 0);

allocatePorts();

$main = new Worker("tcp://0.0.0.0:3406");
$main->onMessage = function ($connection, $data)
{
    $data = json_decode($data, true);
    if (! $data) {
        $connection->close();
        return;
    }
    if ($data["version"] !== PROTOCOL_VERSION) {
        $connection->send(
                json_encode(
                        Array(
                                "version" => PROTOCOL_VERSION,
                                "status" => 1,
                                "msg" => "Protocol version wrong! Please use {PROTOCOL_VERSION}"
                        )));
    }
    if ($data["type"] == "add") {}
};

$monitor = new Worker("tcp://127.0.0.1:" . $monitorport);
$monitor->onWorkerStart = function ($worker)
{};

Worker::runAll();

