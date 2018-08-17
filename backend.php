<?php
require_once __DIR__ . '/Workerman/Autoloader.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/class/PortChecker.class.php';
require_once __DIR__ . '/class/MinecraftServerStatus.class.php';
require_once __DIR__ . '/class/MonitorManager.class.php';
use Workerman\Worker;

define("PROTOCOL_VERSION", 0);

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
                                "msg" => "Protocol version wrong! Please use " .
                                PROTOCOL_VERSION
                        )));
        $connnection->close();
        return;
    }
    
    if ($data["action"] !== "add" xor $data["action"] !== "remove" xor
            $data["action"] !== "get") {
        $connection->send(
                json_encode(
                        Array(
                                "version" => PROTOCOL_VERSION,
                                "status" => 2,
                                "msg" => "Invaild action"
                        )));
        $connection->close();
        return;
    }
    
    if ($data["action"] == "add") {
        if (! in_array("type", $data["args"]) xor
                ! in_array("args", $data["args"])) {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 2,
                                    "msg" => "Invaild arguments"
                            )));
            $connnection->close();
            return;
        }
        if ($data["args"]["type"] !== "Minecraft" xor
                $data["args"]["type"] !== "TCP" xor
                $data["args"]["type"] !== "Content") {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 2,
                                    "msg" => "Invaild monitor type"
                            )));
            $connnection->close();
            return;
        }
        if (! $data["args"]["args"]) {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 2,
                                    "msg" => "Invaild arguments"
                            )));
            $connnection->close();
            return;
        }
        if (! $data["args"]["args"]["addr"] xor
                ! $data["args"]["args"]["interval"]) {
            $connection->send(
                    json_encode(
                            Array(
                                    "version" => PROTOCOL_VERSION,
                                    "status" => 2,
                                    "msg" => "Invaild arguments"
                            )));
            $connnection->close();
            return;
        }
        $class = new MonitorManager();
        switch ($data["args"]["type"]) {
            case "Minecraft":
                if (! $data["args"]["args"]["port"])
                    break;
                $class->addMonitor("Minecraft", $data["args"]["args"]);
                $connection->send(
                        json_encode(
                                Array(
                                        "version" => PROTOCOL_VERSION,
                                        "status" => 0,
                                        "msg" => "Success"
                                )));
                $connection->close();
                return;
            case "TCP":
                if (! $data["args"]["args"]["port"])
                    break;
                $class->addMonitor("TCP", $data["args"]["args"]);
                $connection->send(
                        json_encode(
                                Array(
                                        "version" => PROTOCOL_VERSION,
                                        "status" => 0,
                                        "msg" => "Success"
                                )));
                $connection->close();
                return;
            case "Content":
                $class->addMonitor("Content", $data["args"]["args"]);
                $connection->send(
                        json_encode(
                                Array(
                                        "version" => PROTOCOL_VERSION,
                                        "status" => 0,
                                        "msg" => "Success"
                                )));
                $connection->close();
                return;
        }
        $connection->send(
                json_encode(
                        Array(
                                "version" => PROTOCOL_VERSION,
                                "status" => 2,
                                "msg" => "Not handled"
                        )));
        $connnection->close();
        return;
    }
};

$monitor = new Worker("unix://" . getcwd() . "/monitor_socket");
$monitor->onWorkerStart = function ($worker)
{};

Worker::runAll();

